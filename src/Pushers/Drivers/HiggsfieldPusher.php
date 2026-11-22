<?php

namespace NextDeveloper\Flow\Pushers\Drivers;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use NextDeveloper\Commons\Database\Models\PusherLogs;
use NextDeveloper\Commons\Database\Models\Pushers;
use NextDeveloper\Commons\Pushers\AbstractPusher;
use NextDeveloper\Commons\Pushers\PusherResult;
use NextDeveloper\Flow\Database\Models\Items;
use NextDeveloper\Flow\Services\Higgsfield\HiggsfieldClient;
use NextDeveloper\Flow\Services\Higgsfield\HiggsfieldException;
use NextDeveloper\Flow\Services\HiggsfieldGenerationsService;

/**
 * Generates an image or a video with Higgsfield for a Flow item and stores the result on that item.
 *
 * Provider key: higgsfield
 *
 * The pusher framework is synchronous (one send() call, one terminal log status), but a generation takes
 * seconds to minutes. So send() only SUBMITS the generation and records it; the log then completes with
 * "submitted". The result arrives later through the webhook (or the polling fallback) and is stored as a
 * common_media row on the Flow item - see HiggsfieldGenerationsService.
 *
 * Expected payload keys (the Flow automation payload):
 *   id / flow_item_id  - UUID of the Flow item the result is attached to
 *   anything else      - available to the {{ placeholders }} of prompt_template and params
 *
 * Credentials: the environment (HIGGSFIELD_API_KEY_ID, HIGGSFIELD_API_KEY_SECRET, HIGGSFIELD_WEBHOOK_SECRET) are the
 * platform credentials. A pusher may instead name its own account's credentials with the optional
 * provider_metadata key external_service_id (a Commons ExternalServices row: configuration.key_id, encrypted
 * token = key secret, refresh_token = webhook secret).
 *
 * Required provider_metadata keys:
 *   model_path          - Higgsfield model endpoint, e.g. higgsfield-ai/soul/standard
 *   media_type          - image | video
 *   prompt_template     - prompt with {{ placeholders }}, e.g. "Poster for {{ object.name }}"
 */
class HiggsfieldPusher extends AbstractPusher
{
    // Every generation costs real money, so the default is conservative; raise it per pusher on purpose.
    private const DEFAULT_HOURLY_LIMIT = 10;

    public static function provider(): string
    {
        return 'higgsfield';
    }

    /**
     * Objects this pusher works with (used for UI filtering).
     */
    public static function supportedObjects(): array
    {
        return [
            Items::class,
        ];
    }

    /**
     * Self-description used by GET /commons/pushers/providers.
     */
    public static function definition(): array
    {
        return array_merge(parent::definition(), [
            'label'                 => 'Higgsfield Generator',
            'description'           => 'Generates an image or video with Higgsfield and attaches it to the Flow item.',
            'is_internal'           => true,
            'requires_url'          => false,
            'requires_token'        => false,
            'supports_auth'         => false,
            'supports_auto_disable' => true,
            'metadata_fields'       => [
                ['key' => 'external_service_id', 'type' => 'uuid', 'required' => false, 'label' => 'Own Higgsfield credentials (empty = platform credentials)', 'references' => 'NextDeveloper\\Commons\\Database\\Models\\ExternalServices'],
                ['key' => 'model_path', 'type' => 'string', 'required' => true, 'label' => 'Model path (e.g. higgsfield-ai/soul/standard)'],
                ['key' => 'media_type', 'type' => 'enum', 'required' => true, 'label' => 'Media type', 'options' => ['image', 'video'], 'default' => 'image'],
                ['key' => 'prompt_template', 'type' => 'text', 'required' => true, 'label' => 'Prompt (supports {{ placeholders }} from the item payload)'],
                ['key' => 'params', 'type' => 'json', 'required' => false, 'label' => 'Extra model parameters (aspect_ratio, resolution, duration, ...)'],
                ['key' => 'hourly_limit', 'type' => 'integer', 'required' => false, 'label' => 'Hourly limit (generations)', 'default' => self::DEFAULT_HOURLY_LIMIT],
                ['key' => 'max_cost_usd', 'type' => 'string', 'required' => false, 'label' => 'Max cost per generation (USD)'],
                ['key' => 'daily_budget_usd', 'type' => 'string', 'required' => false, 'label' => 'Daily budget of the account (USD)'],
            ],
        ]);
    }

    /**
     * Rejects a misconfigured pusher when it is saved, instead of failing every push later.
     */
    public static function validateProviderMetadata(array $metadata): void
    {
        $errors = [];

        if (($metadata['external_service_id'] ?? '') !== '' && (!is_string($metadata['external_service_id']) || !\Illuminate\Support\Str::isUuid($metadata['external_service_id']))) {
            $errors['provider_metadata.external_service_id'] = ['external_service_id must be the uuid of the Higgsfield credentials, or empty to use the platform credentials.'];
        }

        try {
            HiggsfieldClient::cleanPath((string) ($metadata['model_path'] ?? ''));
        } catch (HiggsfieldException) {
            $errors['provider_metadata.model_path'] = ['model_path must be a plain Higgsfield model path such as higgsfield-ai/soul/standard.'];
        }

        if (!in_array($metadata['media_type'] ?? null, ['image', 'video'], true)) {
            $errors['provider_metadata.media_type'] = ['media_type must be image or video.'];
        }

        if (!is_string($metadata['prompt_template'] ?? null) || trim($metadata['prompt_template']) === '') {
            $errors['provider_metadata.prompt_template'] = ['prompt_template is required.'];
        }

        if (isset($metadata['params']) && !is_array($metadata['params'])) {
            $errors['provider_metadata.params'] = ['params must be an object.'];
        }

        foreach (['max_cost_usd', 'daily_budget_usd'] as $key) {
            if (isset($metadata[$key]) && $metadata[$key] !== '' && (!is_numeric($metadata[$key]) || (float) $metadata[$key] < 0)) {
                $errors['provider_metadata.' . $key] = [$key . ' must be a positive number of US dollars.'];
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Higgsfield rejects bad keys and empty balances with 401/403; retrying cannot fix those, so the pusher may
     * be auto-disabled after repeated permanent failures.
     */
    protected function supportsAutoDisable(): bool
    {
        return true;
    }

    public function send(PusherLogs $log, Pushers $pusher): PusherResult
    {
        $metadata = (array) $pusher->provider_metadata;
        $body = $this->decodeBody($log);

        $itemUuid = $body['flow_item_id'] ?? $body['id'] ?? null;

        if (empty($itemUuid)) {
            return PusherResult::fail(422, 'Missing flow_item_id in payload.');
        }

        $item = Items::withoutGlobalScopes()->where('uuid', $itemUuid)->first();

        if (!$item || (int) $item->iam_account_id !== (int) $pusher->iam_account_id) {
            Log::warning('[HiggsfieldPusher] Flow item not found for this pusher account.', [
                'pusher_log_id' => $log->id,
                'flow_item_id'  => $itemUuid,
            ]);

            return PusherResult::fail(404, 'Flow item not found: ' . $itemUuid);
        }

        // A retried push of the same log must not submit (and pay for) a second generation.
        $existing = HiggsfieldGenerationsService::findForLog((int) $log->id);

        if ($existing && !empty(data_get($existing->custom_properties, 'request_id'))
            && data_get($existing->custom_properties, 'status') !== HiggsfieldGenerationsService::STATUS_FAILED) {
            return PusherResult::ok(200, 'Skipped: generation already submitted: ' . $existing->uuid);
        }

        // Credentials first: it decides which limits apply. The platform credentials are only for allow-listed
        // accounts and always run under platform-side ceilings the pusher's own metadata cannot raise.
        try {
            $service = HiggsfieldGenerationsService::externalServiceFor(
                $metadata['external_service_id'] ?? null,
                (int) $pusher->iam_account_id
            );
        } catch (HiggsfieldException $e) {
            Log::warning('[HiggsfieldPusher] Credentials refused.', [
                'pusher_log_id' => $log->id,
                'http_status'   => $e->httpStatus,
                'error'         => $e->getMessage(),
            ]);

            return PusherResult::fail($e->httpStatus ?: 422, $e->getMessage());
        }

        if ($service === null) {
            $metadata = array_merge($metadata, HiggsfieldGenerationsService::platformLimits($metadata));
        }

        // Prompt and parameters are rendered from the payload. A rendered prompt may not be empty.
        $prompt = trim((string) HiggsfieldGenerationsService::render((string) ($metadata['prompt_template'] ?? ''), $body));

        if ($prompt === '') {
            return PusherResult::fail(422, 'The prompt rendered empty. Check prompt_template and the payload.');
        }

        $requestBody = array_merge(
            (array) HiggsfieldGenerationsService::render((array) ($metadata['params'] ?? []), $body),
            ['prompt' => $prompt]
        );

        $modelPath = (string) ($metadata['model_path'] ?? '');
        $mediaType = (string) ($metadata['media_type'] ?? 'image');

        try {
            $client = HiggsfieldGenerationsService::clientFor($service);

            // Spend guards run before anything is submitted, using Higgsfield's own estimate for this exact request.
            $estimate = $client->estimate($modelPath, $requestBody);
            $usd = isset($estimate['usd']) && is_numeric($estimate['usd']) ? (float) $estimate['usd'] : null;

            // Check and reservation must be one atomic step per account: without the lock, parallel pushes all read the
            // same "spent so far" and together overshoot the limits. The generation row (with its estimated cost) is
            // created inside the lock by submit(), so the next push already counts it, even while it is still pending.
            $lock = Cache::lock('higgsfield:account:' . (int) $pusher->iam_account_id, 60);

            try {
                $lock->block(15);
            } catch (LockTimeoutException) {
                return PusherResult::fail(429, 'Another Higgsfield generation of this account is being submitted. Try again shortly.');
            }

            try {
                $limit = (int) ($metadata['hourly_limit'] ?? self::DEFAULT_HOURLY_LIMIT);

                if (!$this->isWithinLimit($pusher, $service === null, $limit)) {
                    Log::warning('[HiggsfieldPusher] Hourly generation limit reached - skipping.', [
                        'pusher_log_id' => $log->id,
                        'pusher_id'     => $pusher->id,
                        'hourly_limit'  => $limit,
                    ]);

                    return PusherResult::fail(429, 'Hourly generation limit of ' . $limit . ' reached. Skipping.');
                }

                $guard = HiggsfieldGenerationsService::budgetGuard($metadata, $usd, (int) $pusher->iam_account_id);

                if ($guard !== null) {
                    return PusherResult::fail(422, $guard);
                }

                $media = HiggsfieldGenerationsService::submit(
                    $pusher,
                    $log,
                    $item,
                    $service,
                    $modelPath,
                    $mediaType,
                    $requestBody,
                    $usd !== null ? (string) $estimate['usd'] : null
                );
            } finally {
                $lock->release();
            }

            Log::info('[HiggsfieldPusher] Generation submitted.', [
                'pusher_log_id' => $log->id,
                'flow_item_id'  => $itemUuid,
                'media_uuid'    => $media->uuid,
                'model_path'    => $modelPath,
            ]);

            // 202: accepted, the result follows asynchronously (webhook / poller) as a media row on the item.
            return PusherResult::ok(202, 'Generation submitted: ' . $media->uuid);
        } catch (HiggsfieldException $e) {
            Log::warning('[HiggsfieldPusher] Higgsfield refused or could not be reached.', [
                'pusher_log_id' => $log->id,
                'http_status'   => $e->httpStatus,
                'error'         => $e->getMessage(),
            ]);

            // Pass Higgsfield's meaning on: 401 bad key, 403 empty balance (permanent, may auto-disable), 429 queue full,
            // 5xx / transport (status 0) are reported as 502 because the upstream service failed.
            $code = $e->httpStatus === 0 || $e->httpStatus >= 500 ? 502 : $e->httpStatus;

            return PusherResult::fail($code, $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[HiggsfieldPusher] Failed to submit generation.', [
                'pusher_log_id' => $log->id,
                'flow_item_id'  => $itemUuid,
                'error'         => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
            ]);

            return PusherResult::fail(500, 'Failed to submit generation: ' . $e->getMessage());
        }
    }

    /**
     * Hourly ceiling. On the platform credentials it is counted over every Higgsfield generation of the account
     * (all pushers, including ones still pending), so splitting pushes across pushers does not multiply the limit.
     * On the account's own credentials it stays per pusher.
     */
    private function isWithinLimit(Pushers $pusher, bool $platformCredentials, int $limit): bool
    {
        if ($platformCredentials) {
            return HiggsfieldGenerationsService::generationsInLastHour((int) $pusher->iam_account_id) < $limit;
        }

        $recentCount = PusherLogs::withoutGlobalScopes()
            ->where('common_pusher_id', $pusher->id)
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subHour())
            ->count();

        return $recentCount < $limit;
    }
}
