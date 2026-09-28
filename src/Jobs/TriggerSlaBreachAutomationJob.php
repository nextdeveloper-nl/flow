<?php

namespace NextDeveloper\Flow\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use NextDeveloper\Flow\Services\ItemsService;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * Fires one sla_breached automation for one flow item.
 *
 * The hourly SLA check used to trigger every automation inline — transforming
 * the item and its object and writing a pusher log per breached item — which made
 * the scheduled command run long enough to hold up the rest of the schedule. The
 * check now only dispatches this job per (item, automation) pair; the heavy work
 * runs on the flow queue workers instead.
 *
 * Only primitive ids are serialized, so the job always acts on fresh data.
 */
class TriggerSlaBreachAutomationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const QUEUE_NAME = 'flow';

    // No automatic retries: a retry could fire the same automation twice. The next
    // hourly SLA check re-dispatches the item if it is still breached.
    public int $tries = 1;

    public int $timeout = 120;

    // False when the push already ran for this stage visit; only the event fires.
    // A plain property with a default (not constructor-promoted) so jobs queued
    // before this field existed still unserialize with firePusher = true.
    public bool $firePusher = true;

    public function __construct(
        public int $flowItemId,
        public int $flowAutomationId,
        public int $flowStageId,
        bool $firePusher = true
    ) {
        $this->firePusher = $firePusher;
        $this->queue      = self::QUEUE_NAME;
    }

    public function handle(): void
    {
        UserHelper::setAdminAsCurrentUser();

        ItemsService::fireSlaBreachAutomation($this->flowItemId, $this->flowAutomationId, $this->flowStageId, $this->firePusher);
    }
}
