<?php

namespace NextDeveloper\Flow\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use NextDeveloper\Flow\Services\HiggsfieldGenerationsService;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * Finishes one Higgsfield generation after its webhook arrived: reads the final status from the API,
 * downloads the image/video and stores it on the generation's media row.
 *
 * The webhook must be answered within 10 seconds, while downloading a video can take minutes, so the webhook
 * controller only verifies the signature and dispatches this job. HiggsfieldGenerationsService::finalize() is
 * idempotent, so a repeated webhook or the polling fallback finishing first is harmless.
 *
 * Only the media uuid is serialized so the job always acts on fresh data.
 */
class HiggsfieldFinalizeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const QUEUE_NAME = 'flow';

    // finalize() never throws for provider problems and re-queues itself through the poller, so a
    // second attempt is only for an unexpected crash (worker killed mid-download).
    public int $tries = 2;

    // Downloading a video can be slow. Kept far below the queue retry_after (3900s) so the job is never re-delivered while running.
    public int $timeout = 300;

    public function __construct(public string $mediaUuid)
    {
        $this->queue = self::QUEUE_NAME;
    }

    public function handle(): void
    {
        UserHelper::setAdminAsCurrentUser();

        $result = HiggsfieldGenerationsService::finalize($this->mediaUuid);

        // Webhook said done but the API is not (or storing failed transiently): the poller takes over.
        if ($result === 'pending') {
            HiggsfieldPollJob::dispatch($this->mediaUuid)
                ->delay(now()->addSeconds(HiggsfieldGenerationsService::FIRST_POLL_DELAY_SECONDS));
        }
    }
}
