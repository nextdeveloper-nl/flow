<?php

namespace NextDeveloper\Flow\Console\Commands;

use Illuminate\Console\Command;
use NextDeveloper\IAM\Helpers\UserHelper;
use NextDeveloper\Flow\Database\Models\Automations;
use NextDeveloper\Flow\Database\Models\Items;
use NextDeveloper\Flow\Database\Models\Stages;
use NextDeveloper\Flow\Jobs\CheckSlaBreachesJob;
use NextDeveloper\Flow\Services\ItemsService;
use NextDeveloper\Events\Services\Events;

/**
 * Manually runs the SLA breach check for testing purposes.
 *
 * Examples:
 *   php artisan flow:check-sla-breaches          (dispatches to queue)
 *   php artisan flow:check-sla-breaches --sync   (runs inline, fires automations)
 *   php artisan flow:check-sla-breaches --dry-run (diagnoses without firing automations)
 */
class CheckSlaBreachesCommand extends Command
{
    protected $signature = 'flow:check-sla-breaches
        {--sync    : Run synchronously in this process instead of dispatching to the queue}
        {--dry-run : Show breached items and automations without firing anything}';

    protected $description = 'Check for SLA breaches across all active flow items and fire sla_breached automations';

    public function handle(): int
    {
        UserHelper::setAdminAsCurrentUser();

        $dryRun = $this->option('dry-run');

        if ($dryRun || $this->option('sync')) {
            return $this->runDiagnostic($dryRun);
        }

        CheckSlaBreachesJob::dispatch();
        $this->info('SLA breach check job dispatched to the queue.');

        return self::SUCCESS;
    }

    private function runDiagnostic(bool $dryRun): int
    {
        $label = $dryRun ? '[DRY RUN] ' : '';

        // Step 1: stages with SLA configured
        $slaStages = Stages::withoutGlobalScopes()
            ->whereNotNull('sla_days')
            ->where('is_won', false)
            ->where('is_lost', false)
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('id');

        $this->info("{$label}Stages with SLA configured: " . $slaStages->count());

        if ($slaStages->isEmpty()) {
            $this->warn('No stages have sla_days set — nothing to check.');
            return self::SUCCESS;
        }

        foreach ($slaStages as $stage) {
            $this->line("  Stage [{$stage->uuid}] \"{$stage->name}\" — sla_days: {$stage->sla_days}");
        }

        // Step 2: items sitting in those stages
        $items = Items::withoutGlobalScopes()
            ->whereIn('flow_stage_id', $slaStages->keys())
            ->whereNotNull('last_stage_changed_at')
            ->whereNull('deleted_at')
            ->get();

        $this->info("{$label}Active items in SLA stages: " . $items->count());

        if ($items->isEmpty()) {
            $this->warn('No active items found in any SLA-configured stage.');
            return self::SUCCESS;
        }

        // Step 3: evaluate each item
        $breachedCount = 0;

        foreach ($items as $item) {
            $stage      = $slaStages->get($item->flow_stage_id);
            $daysInStage = (int) $item->last_stage_changed_at->diffInDays(now());
            $breached    = $daysInStage > $stage->sla_days;

            $status = $breached ? '<fg=red>BREACHED</>' : '<fg=green>OK</>';
            $this->line(
                "  Item [{$item->uuid}] stage \"{$stage->name}\" — "
                . "{$daysInStage}d in stage / {$stage->sla_days}d SLA — {$status}"
            );

            if (!$breached) {
                continue;
            }

            $breachedCount++;

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

            if ($automations->isEmpty()) {
                $this->line('    No sla_breached automations configured for this pipeline/stage.');
                continue;
            }

            foreach ($automations as $automation) {
                $this->line(
                    "    Automation [{$automation->uuid}] \"{$automation->name}\" "
                    . "— pusher: " . ($automation->common_pusher_id ?? 'none')
                    . ", event: " . ($automation->event_name ?? 'none')
                );

                if (!$dryRun) {
                    if ($automation->common_pusher_id) {
                        ItemsService::triggerPusherForAutomation($automation, $item);
                    }

                    if ($automation->event_name) {
                        Events::fire($automation->event_name, $item);
                    }
                }
            }
        }

        $this->info("{$label}Breached items: {$breachedCount} / " . $items->count());

        if (!$dryRun && $breachedCount > 0) {
            $this->info('Automations fired for all breached items.');
        }

        return self::SUCCESS;
    }
}
