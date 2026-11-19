<?php

namespace NextDeveloper\Flow\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Database\Models\ExternalServices;
use NextDeveloper\Commons\Database\Models\Media;
use NextDeveloper\Commons\Database\Models\PusherLogs;
use NextDeveloper\Commons\Database\Models\Pushers;
use NextDeveloper\Commons\Services\MediaService;
use NextDeveloper\Flow\Database\Models\Items;
use NextDeveloper\Flow\Jobs\HiggsfieldFinalizeJob;
use NextDeveloper\Flow\Jobs\HiggsfieldPollJob;
use NextDeveloper\Flow\Services\Higgsfield\HiggsfieldClient;
use NextDeveloper\Flow\Services\Higgsfield\HiggsfieldException;
use NextDeveloper\IAM\Database\Models\Accounts;
use NextDeveloper\IAM\Database\Models\Users;

/**
 * Owns every write and decision of a Higgsfield image/video generation.
 *
 * There is no table of its own: one `common_media` row per generation, created as a placeholder when the
 * generation is submitted and filled with the file when it completes.
 *   - tags              ['higgsfield', 'higgsfield:image'|'higgsfield:video']  (how Higgsfield rows are found)
 *   - object_type/id    the Flow item the result belongs to
 *   - manipulations     how it was made: model_path, media_type, the request body (params)
 *   - custom_properties the state: status, request_id, pusher ids, estimated_usd, timestamps, error
 *
 * Both completion paths (webhook and polling fallback) end in finalize(), which claims the row first so a
 * webhook retry or a poll racing the webhook can never store the result twice.
 */
class HiggsfieldGenerationsService
{
    public const TAG = 'higgsfield';
    public const COLLECTION = 'higgsfield';

    // custom_properties.status values
    public const STATUS_PENDING = 'pending';       // row created, not yet accepted by Higgsfield
    public const STATUS_SUBMITTED = 'submitted';   // accepted, waiting for the result
    public const STATUS_FINALIZING = 'finalizing'; // claimed by a finalize() call that is downloading the file
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_NSFW = 'nsfw';
    public const STATUS_CANCELED = 'canceled';

    // A claim older than this is considered abandoned (the worker died) and can be taken over.
    private const CLAIM_TIMEOUT_MINUTES = 10;

    // Webhook timestamps further than this from now are rejected (replay protection, per Standard Webhooks).
    private const WEBHOOK_TOLERANCE_SECONDS = 300;

    // First status check when no webhook arrives.
    public const FIRST_POLL_DELAY_SECONDS = 90;

    /**
     * Public URL Higgsfield calls when the generation finishes. The media uuid identifies the generation;
     * authenticity comes from the Standard Webhooks signature, not from the URL.
     */
    public static function webhookUrl(string $mediaUuid): string
    {
        return rtrim((string) config('app.url'), '/') . '/public/flow/higgsfield/webhook/' . $mediaUuid;
    }

    /**
     * The Higgsfield credentials row of this account, or null when the pusher uses the platform credentials
     * from the environment (HIGGSFIELD_API_KEY_ID / HIGGSFIELD_API_KEY_SECRET / HIGGSFIELD_WEBHOOK_SECRET).
     *
     * A per-account row holds the key id in `configuration.key_id`, the key secret in the encrypted `token` and the
     * webhook secret in the encrypted `refresh_token`. A row of another account is never usable.
     *
     * @throws HiggsfieldException when a row is requested but missing or foreign
     */
    public static function externalServiceFor(?string $externalServiceUuid, int $accountId): ?ExternalServices
    {
        if ($externalServiceUuid === null || $externalServiceUuid === '') {
            // The platform credentials spend our own Higgsfield balance: only allow-listed accounts may use them.
            if (!self::platformCredentialsAllowed($accountId)) {
                throw new HiggsfieldException(
                    'This account may not use the platform Higgsfield credentials. Set external_service_id to your own credentials.',
                    403
                );
            }

            return null;
        }

        $service = ExternalServices::withoutGlobalScopes()->where('uuid', $externalServiceUuid)->first();

        if (!$service || (int) $service->iam_account_id !== $accountId) {
            throw new HiggsfieldException('Higgsfield credentials (external service) not found for this account.', 422);
        }

        return $service;
    }

    /**
     * Whether the account is allow-listed (HIGGSFIELD_PLATFORM_ACCOUNT_IDS, account uuids) to spend the platform credentials.
     */
    public static function platformCredentialsAllowed(int $accountId): bool
    {
        $uuid = Accounts::withoutGlobalScopes()->where('id', $accountId)->value('uuid');

        return $uuid !== null && in_array((string) $uuid, (array) config('higgsfield.platform_account_ids', []), true);
    }

    /**
     * Limits for a pusher that runs on the platform credentials: the pusher's own values may only lower the
     * platform ceilings, and an unset value falls back to the ceiling (a tenant cannot leave a limit off).
     *
     * @return array{hourly_limit: int, max_cost_usd: float, daily_budget_usd: float}
     */
    public static function platformLimits(array $metadata): array
    {
        $cap = fn (string $key, string $config) => ($metadata[$key] ?? '') !== '' && is_numeric($metadata[$key])
            ? min((float) $metadata[$key], (float) config($config))
            : (float) config($config);

        return [
            'hourly_limit'     => (int) $cap('hourly_limit', 'higgsfield.platform_hourly_limit'),
            'max_cost_usd'     => $cap('max_cost_usd', 'higgsfield.platform_max_cost_usd'),
            'daily_budget_usd' => $cap('daily_budget_usd', 'higgsfield.platform_daily_budget_usd'),
        ];
    }

    /**
     * HTTP client for a credentials row, or for the platform credentials of the environment when there is no row.
     *
     * @throws HiggsfieldException when key id or secret is missing
     */
    public static function clientFor(?ExternalServices $service): HiggsfieldClient
    {
        $keyId = $service ? (string) data_get($service->configuration, 'key_id') : (string) config('higgsfield.key_id');
        $secret = $service ? self::decryptOrRaw($service->token) : (string) config('higgsfield.key_secret');

        if ($keyId === '' || $secret === '') {
            throw new HiggsfieldException(
                $service
                    ? 'Higgsfield credentials are incomplete: key_id in configuration and the key secret in token are required.'
                    : 'Higgsfield is not configured: set HIGGSFIELD_API_KEY_ID and HIGGSFIELD_API_KEY_SECRET, or give the pusher an external_service_id.',
                422
            );
        }

        return new HiggsfieldClient($keyId, $secret);
    }

    /**
     * The generation row already created for this pusher log, if any (a retried push finds it instead of
     * creating - and paying for - a second one).
     */
    public static function findForLog(int $pusherLogId): ?Media
    {
        return Media::withoutGlobalScopes()
            ->whereRaw("tags @> ARRAY['" . self::TAG . "']::text[]")
            ->whereRaw("custom_properties->>'pusher_log_id' = ?", [(string) $pusherLogId])
            ->first();
    }

    /**
     * USD estimated for this account's Higgsfield generations since midnight. Failed, NSFW and canceled
     * generations are not charged by Higgsfield, so they do not count.
     */
    public static function spentTodayUsd(int $accountId): float
    {
        return (float) Media::withoutGlobalScopes()
            ->whereRaw("tags @> ARRAY['" . self::TAG . "']::text[]")
            ->where('iam_account_id', $accountId)
            ->where('created_at', '>=', now()->startOfDay())
            ->whereRaw("COALESCE(custom_properties->>'status', '') NOT IN ('failed', 'nsfw', 'canceled')")
            ->sum(DB::raw("COALESCE((custom_properties->>'estimated_usd')::numeric, 0)"));
    }

    /**
     * Records the generation (placeholder row) and submits it to Higgsfield. Safe to call again for the same
     * pusher log: an already submitted generation is returned as it is, and a resubmission after a crash
     * between the two steps carries the same Idempotency-Key, so Higgsfield returns the original request.
     *
     * @throws HiggsfieldException when Higgsfield refuses or cannot be reached (the row is marked failed)
     */
    public static function submit(
        Pushers $pusher,
        PusherLogs $log,
        Items $item,
        ?ExternalServices $service,
        string $modelPath,
        string $mediaType,
        array $body,
        ?string $estimatedUsd
    ): Media {
        $media = self::findForLog((int) $log->id)
            ?? self::createPending($pusher, $log, $item, $service, $modelPath, $mediaType, $body, $estimatedUsd);

        $props = (array) $media->custom_properties;

        if (!empty($props['request_id']) && ($props['status'] ?? null) !== self::STATUS_FAILED) {
            return $media;
        }

        self::updateProperties($media, ['status' => self::STATUS_PENDING, 'error' => null]);

        try {
            $response = self::clientFor($service)->submit(
                $modelPath,
                $body,
                (string) $log->uuid,
                self::webhookUrl((string) $media->uuid)
            );
        } catch (HiggsfieldException $e) {
            self::fail($media, self::STATUS_FAILED, $e->getMessage());

            throw $e;
        }

        self::updateProperties($media, [
            'status'     => self::STATUS_SUBMITTED,
            'request_id' => $response['request_id'] ?? null,
            'submitted_at' => now()->toIso8601String(),
        ]);

        // No webhook within the first minutes? The poller checks the status and finishes the generation.
        HiggsfieldPollJob::dispatch((string) $media->uuid)->delay(now()->addSeconds(self::FIRST_POLL_DELAY_SECONDS));

        return $media->fresh();
    }

    /**
     * Completes the generation if Higgsfield has finished it: downloads the output, stores it on the media
     * row and marks it completed - or records the failure. Idempotent and safe to run concurrently.
     *
     * @return string 'completed' | 'failed' (terminal, recorded) | 'pending' (not finished, or a transient
     *                error, try again later) | 'noop' (nothing to do: unknown row or already terminal/claimed)
     */
    public static function finalize(string $mediaUuid): string
    {
        $props = self::claim($mediaUuid);

        if ($props === null) {
            return 'noop';
        }

        $media = Media::withoutGlobalScopes()->where('uuid', $mediaUuid)->first();

        try {
            // No row recorded means the generation was submitted with the platform credentials of the environment.
            $service = empty($props['external_service_id'])
                ? null
                : ExternalServices::withoutGlobalScopes()->where('uuid', $props['external_service_id'])->first();

            if (!empty($props['external_service_id']) && !$service) {
                throw new HiggsfieldException('Higgsfield credentials row no longer exists.', 422);
            }

            $remote = self::clientFor($service)->status((string) $props['request_id']);
        } catch (HiggsfieldException $e) {
            if ($e->isTransient()) {
                self::release($media);

                return 'pending';
            }

            self::fail($media, self::STATUS_FAILED, $e->getMessage());

            return 'failed';
        }

        $remoteStatus = (string) ($remote['status'] ?? '');

        if (in_array($remoteStatus, ['failed', 'nsfw', 'canceled'], true)) {
            self::fail($media, $remoteStatus, (string) ($remote['error'] ?? ('Higgsfield reported: ' . $remoteStatus)));

            return 'failed';
        }

        if ($remoteStatus !== 'completed') {
            self::release($media);

            return 'pending';
        }

        $urls = self::outputUrls($remote);

        if ($urls === []) {
            self::fail($media, self::STATUS_FAILED, 'Higgsfield completed the request but returned no downloadable output.');

            return 'failed';
        }

        $mediaType = (string) data_get($media->manipulations, 'media_type', 'image');
        $tmp = sys_get_temp_dir() . '/higgsfield_' . Str::uuid() . '.' . self::extensionFor($urls[0], $mediaType);

        try {
            $download = Http::timeout(240)->sink($tmp)->get($urls[0]);

            if (!$download->successful()) {
                throw new \RuntimeException('Output download answered HTTP ' . $download->status());
            }

            MediaService::attachFile((string) $media->uuid, $tmp, config('higgsfield.storage'), [
                'status'            => self::STATUS_COMPLETED,
                'completed_at'      => now()->toIso8601String(),
                'result_expires_at' => $remote['expires_at'] ?? null,
                // A batch result holds several outputs; the first is stored, all addresses are kept for reference.
                'result_urls'       => $urls,
                'finalizing_at'     => null,
                'error'             => null,
            ]);
        } catch (\Throwable $e) {
            @unlink($tmp);

            // Storing failed (network, disk): leave the row claimable so the poller tries again.
            Log::error('[HiggsfieldGenerations] Could not store the generated file.', [
                'media_uuid' => $mediaUuid,
                'error'      => $e->getMessage(),
            ]);

            self::release($media);

            return 'pending';
        }

        @unlink($tmp);

        Log::info('[HiggsfieldGenerations] Generation stored.', ['media_uuid' => $mediaUuid]);

        return 'completed';
    }

    /**
     * Records a terminal failure (failed | nsfw | canceled | timed out). The row stays as a record without a file.
     */
    public static function fail(Media $media, string $status, string $error): void
    {
        self::updateProperties($media, [
            'status'        => $status,
            'error'         => mb_substr($error, 0, 1000),
            'finalizing_at' => null,
            'completed_at'  => now()->toIso8601String(),
        ]);

        Log::warning('[HiggsfieldGenerations] Generation did not complete.', [
            'media_uuid' => $media->uuid,
            'status'     => $status,
            'error'      => $error,
        ]);
    }

    /**
     * Handles a Higgsfield webhook call: verifies the Standard Webhooks signature against the raw body and
     * queues the finalization (the answer must come back within 10 seconds, the download can take longer).
     * The webhook body is never trusted for the result; finalize() reads the status from the API.
     *
     * @param array<string, string|null> $headers webhook-id, webhook-timestamp, webhook-signature
     * @return int HTTP status to answer with
     */
    public static function handleWebhook(string $mediaUuid, array $headers, string $rawBody): int
    {
        $media = Media::withoutGlobalScopes()
            ->whereRaw("tags @> ARRAY['" . self::TAG . "']::text[]")
            ->where('uuid', $mediaUuid)
            ->first();

        if (!$media) {
            return 404;
        }

        $serviceUuid = data_get($media->custom_properties, 'external_service_id');
        $service = $serviceUuid
            ? ExternalServices::withoutGlobalScopes()->where('uuid', $serviceUuid)->first()
            : null;

        // The webhook secret is shown once in the Higgsfield console. Per-account rows keep it in the encrypted
        // refresh_token; without a row the platform secret from the environment is used.
        $secret = $serviceUuid
            ? (string) ($service?->refresh_token ?? '')
            : (string) config('higgsfield.webhook_secret');

        if ($secret === '' || !self::verifySignature(
            $secret,
            (string) ($headers['webhook-id'] ?? ''),
            (string) ($headers['webhook-timestamp'] ?? ''),
            (string) ($headers['webhook-signature'] ?? ''),
            $rawBody
        )) {
            return 401;
        }

        // Deliveries are retried and can repeat: answer 200 to a repeat without queueing it again.
        if (!Cache::add('higgsfield:webhook:' . $headers['webhook-id'], 1, now()->addDay())) {
            return 200;
        }

        HiggsfieldFinalizeJob::dispatch($mediaUuid);

        return 200;
    }

    /**
     * Standard Webhooks signature check: HMAC-SHA256 over "{id}.{timestamp}.{body}" keyed with the base64-decoded
     * secret (without its "whsec_" prefix); the header holds one or more space separated "v1,<base64>" entries.
     */
    public static function verifySignature(
        string $secret,
        string $webhookId,
        string $timestamp,
        string $signatureHeader,
        string $rawBody,
        ?int $now = null
    ): bool {
        if ($webhookId === '' || $signatureHeader === '' || !ctype_digit($timestamp)) {
            return false;
        }

        if (abs(($now ?? time()) - (int) $timestamp) > self::WEBHOOK_TOLERANCE_SECONDS) {
            return false;
        }

        $key = base64_decode(Str::after($secret, 'whsec_'), true);

        if ($key === false || $key === '') {
            return false;
        }

        $expected = base64_encode(hash_hmac('sha256', $webhookId . '.' . $timestamp . '.' . $rawBody, $key, true));

        foreach (explode(' ', $signatureHeader) as $entry) {
            [$version, $signature] = array_pad(explode(',', $entry, 2), 2, '');

            if ($version === 'v1' && hash_equals($expected, $signature)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Renders {{ path }} placeholders from the push payload (item fields and the nested `object`). A placeholder
     * whose path does not exist renders empty. Applies recursively to arrays (the model `params`).
     */
    public static function render(mixed $template, array $payload): mixed
    {
        if (is_array($template)) {
            return array_map(fn ($value) => self::render($value, $payload), $template);
        }

        if (!is_string($template)) {
            return $template;
        }

        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_.\-]+)\s*\}\}/', function ($match) use ($payload) {
            $value = data_get($payload, $match[1]);

            return is_scalar($value) ? (string) $value : '';
        }, $template);
    }

    /**
     * Creates the placeholder row. Ownership is set explicitly to the pusher's account and user: the code
     * runs as the administrator inside queues and must not stamp the generation on the admin.
     */
    private static function createPending(
        Pushers $pusher,
        PusherLogs $log,
        Items $item,
        ?ExternalServices $service,
        string $modelPath,
        string $mediaType,
        array $body,
        ?string $estimatedUsd
    ): Media {
        $account = Accounts::withoutGlobalScopes()->find($pusher->iam_account_id);
        $user = Users::withoutGlobalScopes()->find($pusher->iam_user_id);

        $created = MediaService::createPending([
            'object_type'       => Items::class,
            'object_id'         => (int) $item->id,
            'collection_name'   => self::COLLECTION,
            'name'              => 'Higgsfield ' . $mediaType . ' ' . now()->format('Y-m-d H:i'),
            'tags'              => [self::TAG, self::TAG . ':' . $mediaType],
            'manipulations'     => [
                'model_path' => $modelPath,
                'media_type' => $mediaType,
                'params'     => $body,
            ],
            'custom_properties' => [
                'source'              => 'higgsfield',
                'status'              => self::STATUS_PENDING,
                'pusher_id'           => $pusher->uuid,
                'pusher_log_id'       => (int) $log->id,
                'external_service_id' => $service?->uuid,
                'estimated_usd'       => $estimatedUsd,
                'created_at'          => now()->toIso8601String(),
            ],
            'iam_account_id'    => $account?->uuid,
            'iam_user_id'       => $user?->uuid,
        ]);

        return Media::withoutGlobalScopes()->where('uuid', $created->uuid)->firstOrFail();
    }

    /**
     * Takes the exclusive right to finalize a submitted generation (status submitted, or finalizing with a stale
     * claim) by flipping it to finalizing under a row lock. Returns its properties, or null when somebody else
     * owns it or it is not ours / not waiting.
     */
    private static function claim(string $mediaUuid): ?array
    {
        return DB::transaction(function () use ($mediaUuid) {
            $media = Media::withoutGlobalScopes()
                ->whereRaw("tags @> ARRAY['" . self::TAG . "']::text[]")
                ->where('uuid', $mediaUuid)
                ->lockForUpdate()
                ->first();

            if (!$media) {
                return null;
            }

            $props = (array) $media->custom_properties;
            $status = $props['status'] ?? null;

            $stale = $status === self::STATUS_FINALIZING
                && now()->subMinutes(self::CLAIM_TIMEOUT_MINUTES)->gt($props['finalizing_at'] ?? now());

            if (($status !== self::STATUS_SUBMITTED && !$stale) || empty($props['request_id'])) {
                return null;
            }

            $props['status'] = self::STATUS_FINALIZING;
            $props['finalizing_at'] = now()->toIso8601String();

            MediaService::update((string) $media->uuid, ['custom_properties' => $props]);

            return $props;
        });
    }

    /**
     * Gives a claimed generation back (status submitted again) so the poller or a webhook can retry it.
     */
    private static function release(Media $media): void
    {
        self::updateProperties($media, ['status' => self::STATUS_SUBMITTED, 'finalizing_at' => null]);
    }

    /**
     * Merges keys into custom_properties and saves through the service.
     */
    private static function updateProperties(Media $media, array $changes): void
    {
        $fresh = Media::withoutGlobalScopes()->where('uuid', $media->uuid)->first() ?? $media;
        $props = array_merge((array) $fresh->custom_properties, $changes);

        MediaService::update((string) $media->uuid, ['custom_properties' => $props]);
    }

    /**
     * Output addresses of a completed request: images[].url, video.url or audio.url. Only https addresses are
     * accepted so the download can never be pointed at an internal service.
     *
     * @return string[]
     */
    private static function outputUrls(array $remote): array
    {
        $urls = array_merge(
            array_column((array) ($remote['images'] ?? []), 'url'),
            [data_get($remote, 'video.url'), data_get($remote, 'audio.url')]
        );

        return array_values(array_filter(
            $urls,
            fn ($url) => is_string($url) && str_starts_with($url, 'https://')
        ));
    }

    private static function extensionFor(string $url, string $mediaType): string
    {
        $extension = strtolower(pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));

        if (preg_match('/^[a-z0-9]{2,5}$/', $extension)) {
            return $extension;
        }

        return $mediaType === 'video' ? 'mp4' : 'png';
    }

    /**
     * The token is stored encrypted; a row saved before encryption still holds the plain value.
     */
    private static function decryptOrRaw(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        try {
            return (string) decrypt($value);
        } catch (\Throwable) {
            return $value;
        }
    }
}
