<?php

namespace NextDeveloper\Flow\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use NextDeveloper\Commons\Database\Models\Media;
use NextDeveloper\Flow\Services\HiggsfieldGenerationsService;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * Polling fallback for a Higgsfield generation: checks the status and finishes the generation when the
 * webhook did not (or has not yet) arrived.
 *
 * It never holds a worker while waiting: when the generation is not finished it dispatches itself again with a
 * delay, the attempt number travelling in the payload (same pattern as IAAS FinalizeAgentImport). It stops as
 * soon as the generation is terminal - including when the webhook finished it in the meantime - and marks the
 * generation failed after the deadline, which matches Higgsfield's two hour webhook retry window.
 */
class HiggsfieldPollJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const QUEUE_NAME = 'flow';

    // 26 checks across roughly two hours (see delaySeconds()).
    public const MAX_ATTEMPTS = 26;

    // Re-dispatches itself instead of retrying, so a failed run must not be repeated by the queue.
    public int $tries = 1;

    // finalize() may download a video. Kept far below the queue retry_after (3900s).
    public int $timeout = 300;

    public function __construct(public string $mediaUuid, public int $attempt = 1)
    {
        $this->queue = self::QUEUE_NAME;
    }

    /**
     * Wait before check number $nextAttempt: 60s for the first ones (images are fast), growing to a 5 minute cap.
     */
    public static function delaySeconds(int $nextAttempt): int
    {
        return min(300, 60 * max(1, intdiv($nextAttempt, 3) + 1));
    }

    public function handle(): void
    {
        UserHelper::setAdminAsCurrentUser();

        $result = HiggsfieldGenerationsService::finalize($this->mediaUuid);

        if (in_array($result, ['completed', 'failed'], true)) {
            return; // recorded
        }

        $media = Media::withoutGlobalScopes()->where('uuid', $this->mediaUuid)->first();
        $status = data_get($media?->custom_properties, 'status');

        // noop: the row is unknown, finished elsewhere (webhook), or claimed by a finalize that is still running.
        // Keep polling only while the generation is still open, so a crashed finalize (stale claim) is taken over.
        if ($result === 'noop' && !in_array($status, [
            HiggsfieldGenerationsService::STATUS_SUBMITTED,
            HiggsfieldGenerationsService::STATUS_FINALIZING,
        ], true)) {
            return;
        }

        if ($this->attempt >= self::MAX_ATTEMPTS) {
            if ($media) {
                HiggsfieldGenerationsService::fail(
                    $media,
                    HiggsfieldGenerationsService::STATUS_FAILED,
                    'Timed out waiting for Higgsfield to finish the generation.'
                );
            }

            return;
        }

        self::dispatch($this->mediaUuid, $this->attempt + 1)
            ->delay(now()->addSeconds(self::delaySeconds($this->attempt + 1)));
    }
}
