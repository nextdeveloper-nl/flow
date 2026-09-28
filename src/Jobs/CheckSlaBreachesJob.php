<?php

namespace NextDeveloper\Flow\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use NextDeveloper\Flow\Services\ItemsService;
// Was missing — handle() calls UserHelper::setAdminAsCurrentUser(), so the queued path crashed with "class not found".
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * Detects items that have exceeded their stage SLA and fires sla_breached automations.
 * Intended to be dispatched on a schedule (e.g. hourly).
 *
 * The detection and dispatch logic lives in ItemsService::checkSlaBreaches(), shared
 * with flow:check-sla-breaches; each automation then runs in its own
 * TriggerSlaBreachAutomationJob.
 */
class CheckSlaBreachesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const QUEUE_NAME = 'flow';

    // A retry would dispatch every automation job again — rely on the next hourly run instead.
    public int $tries = 1;

    public int $timeout = 120;

    public function __construct()
    {
        $this->queue = self::QUEUE_NAME;
    }

    public function handle(): void
    {
        UserHelper::setAdminAsCurrentUser();

        ItemsService::checkSlaBreaches();
    }
}
