<?php

namespace NextDeveloper\Flow\Services\Higgsfield;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Thin HTTP wrapper around the Higgsfield REST API (https://docs.higgsfield.ai).
 *
 * It only talks HTTP: it never touches the database or models. Authentication is
 * `Authorization: Key <key_id>:<key_secret>`, server side only. Every failure is raised as a
 * HiggsfieldException so callers handle one error type.
 */
class HiggsfieldClient
{
    public const DEFAULT_BASE_URL = 'https://api.higgsfield.ai';

    public function __construct(
        private readonly string $keyId,
        private readonly string $keySecret,
        private readonly ?string $baseUrl = null,
    ) {
    }

    /**
     * Base URL of the API; overridable through config('higgsfield.base_url') (for tests or a proxy).
     */
    public function baseUrl(): string
    {
        return rtrim($this->baseUrl ?: config('higgsfield.base_url', self::DEFAULT_BASE_URL), '/');
    }

    /**
     * Cost estimate for a generation, in USD, using the same body as the real submission.
     *
     * @return array{usd: string}
     */
    public function estimate(string $modelPath, array $body): array
    {
        return $this->send('post', '/estimate/' . self::cleanPath($modelPath), $body);
    }

    /**
     * Submits a generation. The Idempotency-Key makes a replay return the original request instead of
     * creating (and charging) a second one. $webhookUrl, when given, is notified on completion.
     *
     * @return array{request_id: string, status: string, status_url?: string, cancel_url?: string, expires_after?: int}
     */
    public function submit(string $modelPath, array $body, string $idempotencyKey, ?string $webhookUrl = null): array
    {
        $path = '/' . self::cleanPath($modelPath);

        if ($webhookUrl) {
            $path .= '?hf_webhook=' . rawurlencode($webhookUrl);
        }

        return $this->send('post', $path, $body, ['Idempotency-Key' => $idempotencyKey]);
    }

    /**
     * Status of a request: queued | in_progress | completed | failed | nsfw | canceled, plus the output
     * (images[].url, video.url, ...) once it is completed.
     */
    public function status(string $requestId): array
    {
        return $this->send('get', '/requests/' . rawurlencode($requestId) . '/status');
    }

    /**
     * Cancels a request that is still queued (Higgsfield refunds it). A request already in progress cannot be canceled.
     */
    public function cancel(string $requestId): void
    {
        $this->send('post', '/requests/' . rawurlencode($requestId) . '/cancel');
    }

    /**
     * Model paths are configured by account users, so they are restricted to a plain relative path on
     * our fixed base URL: no scheme, no host, no traversal, no query string.
     */
    public static function cleanPath(string $modelPath): string
    {
        // A protocol-relative address ("//host") must not turn into a plain path by trimming the slashes.
        $path = str_starts_with($modelPath, '//') ? '' : trim($modelPath, '/');

        if (!preg_match('#^[a-z0-9][a-z0-9._\-]*(/[a-z0-9][a-z0-9._\-]*)*$#i', $path)) {
            throw new HiggsfieldException('Invalid Higgsfield model path: ' . $modelPath, 422);
        }

        return $path;
    }

    private function request(array $extraHeaders = []): PendingRequest
    {
        return Http::baseUrl($this->baseUrl())
            ->withHeaders(array_merge(['Authorization' => 'Key ' . $this->keyId . ':' . $this->keySecret], $extraHeaders))
            ->acceptJson()
            ->asJson()
            ->timeout(30);
    }

    private function send(string $method, string $path, array $body = [], array $headers = []): array
    {
        try {
            $response = $method === 'get'
                ? $this->request($headers)->get($path)
                : $this->request($headers)->post($path, $body ?: null);
        } catch (ConnectionException $e) {
            throw new HiggsfieldException('Could not reach Higgsfield: ' . $e->getMessage(), 0);
        }

        if (!$response->successful()) {
            throw $this->toException($response);
        }

        // cancel answers 202 with an empty body
        return (array) ($response->json() ?? []);
    }

    private function toException(Response $response): HiggsfieldException
    {
        $detail = $response->json('detail');
        $message = is_array($detail)
            ? (string) ($detail['message'] ?? json_encode($detail))
            : (string) ($detail ?? $response->body());

        $retryAfter = $response->header('Retry-After');

        return new HiggsfieldException(
            'Higgsfield API error ' . $response->status() . ': ' . mb_substr($message, 0, 500),
            $response->status(),
            is_numeric($retryAfter) ? (int) $retryAfter : null
        );
    }
}
