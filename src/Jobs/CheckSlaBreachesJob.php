<?php

namespace NextDeveloper\Flow\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use NextDeveloper\Flow\Database\Models\Items;
use NextDeveloper\Flow\Database\Models\Stages;
use NextDeveloper\Flow\Database\Models\Automations;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\Flow\Services\ItemsService;
// Was missing — handle() calls UserHelper::setAdminAsCurrentUser(), so the queued path crashed with "class not found".
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * Detects items that have exceeded their stage SLA and fires sla_breached automations.
 * Intended to be dispatched on a schedule (e.g. hourly).
 */
class CheckSlaBreachesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const QUEUE_NAME = 'flow';

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct()
    {
        $this->queue = self::QUEUE_NAME;
    }

    public function handle(): void
    {
        UserHelper::setAdminAsCurrentUser();

        // Load all non-won/non-lost stages that have an SLA configured.
        // Keyed by ID to avoid N+1 when checking items.
        $slaStages = Stages::withoutGlobalScopes()
            ->whereNotNull('sla_days')
            ->where('is_won', false)
            ->where('is_lost', false)
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('id');

        if ($slaStages->isEmpty()) {
            return;
        }

        // Load all active items currently sitting in one of those stages.
        $items = Items::withoutGlobalScopes()
            ->whereIn('flow_stage_id', $slaStages->keys())
            ->whereNotNull('last_stage_changed_at')
            ->whereNull('deleted_at')
            ->get();

        // Breach status comes from flow_items_perspective.sla_breached (what the UI
        // shows) — the old diffInDays rule is floored by Carbon 2 and fired up to a
        // day after the UI did.
        $breachedIds = ItemsService::getSlaBreachedItemIds();

        foreach ($items as $item) {
            $stage = $slaStages->get($item->flow_stage_id);

            if (!$stage) {
                continue;
            }

            if (!isset($breachedIds[$item->id])) {
                continue;
            }

            $automations = Automations::withoutGlobalScopes()
                ->where('flow_pipeline_id', $item->flow_pipeline_id)
                ->where('trigger', 'sla_breached')
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->where(function ($q) use ($item) {
                    $q->whereNull('flow_stage_id')
                      ->orWhere('flow_stage_id', $item->flow_stage_id);
                })
                ->get();

            foreach ($automations as $automation) {
                if ($automation->common_pusher_id) {
                    ItemsService::triggerPusherForAutomation($automation, $item);
                }

                if (!$automation->event_name) {
                    continue;
                }

                Events::fire($automation->event_name, $item);
            }
        }
    }
}
