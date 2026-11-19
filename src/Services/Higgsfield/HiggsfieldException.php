<?php

namespace NextDeveloper\Flow\Services\Higgsfield;

use RuntimeException;

/**
 * A Higgsfield API call that did not succeed. Carries the HTTP status (0 for a transport
 * failure such as a timeout) so callers can tell a permanent problem (401, 403, 422) from one
 * worth retrying (429, 5xx, transport).
 */
class HiggsfieldException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $httpStatus = 0,
        public readonly ?int $retryAfterSeconds = null,
    ) {
        parent::__construct($message, $httpStatus);
    }

    /**
     * Retrying later can succeed: transport errors, rate limits and server errors.
     */
    public function isTransient(): bool
    {
        return $this->httpStatus === 0
            || $this->httpStatus === 429
            || $this->httpStatus === 423
            || $this->httpStatus >= 500;
    }
}
