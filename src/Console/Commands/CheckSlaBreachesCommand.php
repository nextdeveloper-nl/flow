<?php

namespace NextDeveloper\Flow\Console\Commands;

use Illuminate\Console\Command;
use NextDeveloper\IAM\Helpers\UserHelper;
use NextDeveloper\Flow\Jobs\CheckSlaBreachesJob;
use NextDeveloper\Flow\Services\ItemsService;

/**
 * Runs the SLA breach check.
 *
 * Breached items come from flow_items_perspective.sla_breached (same as the UI).
 * The check itself is cheap; each matching automation is fired by a queued
 * TriggerSlaBreachAutomationJob on the flow queue, so this command finishes
 * quickly regardless of how many items are breached.
 *
 * Examples:
 *   php artisan flow:check-sla-breaches           (dispatches the whole check to the queue)
 *   php artisan flow:check-sla-breaches --sync    (runs the check here, dispatches one job per automation)
 *   php artisan flow:check-sla-breaches --dry-run (lists breached items and automations, dispatches nothing)
 */
class CheckSlaBreachesCommand extends Command
{
    protected $signature = 'flow:check-sla-breaches
        {--sync    : Run the check in this process instead of dispatching it to the queue}
        {--dry-run : Show breached items and automations without firing anything}';

    protected $description = 'Check for SLA breaches across all active flow items and fire sla_breached automations';

    public function handle(): int
    {
        UserHelper::setAdminAsCurrentUser();

        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun || $this->option('sync')) {
            return $this->runCheck($dryRun);
        }

        CheckSlaBreachesJob::dispatch();
        $this->info('SLA breach check job dispatched to the queue.');

        return self::SUCCESS;
    }

    private function runCheck(bool $dryRun): int
    {
        $label  = $dryRun ? '[DRY RUN] ' : '';
        $report = ItemsService::checkSlaBreaches($dryRun);

        $counts = ['dispatched' => 0, 'dry_run' => 0, 'skipped_pending' => 0];

        foreach ($report as $entry) {
            $item  = $entry['item'];
            $stage = $entry['stage'];
            $hours = round($item->last_stage_changed_at->diffInMinutes(now()) / 60, 1);

            $this->line(
                "  Item [{$item->uuid}] stage \"{$stage->name}\" — "
                . "{$hours}h in stage / {$stage->sla_days}d SLA — <fg=red>BREACHED</>"
            );

            if (!$entry['automations']) {
                $this->line('    No sla_breached automations configured for this pipeline/stage.');
                continue;
            }

            foreach ($entry['automations'] as $row) {
                $automation = $row['automation'];
                $counts[$row['status']]++;

                $this->line(
                    "    Automation [{$automation->uuid}] \"{$automation->name}\" "
                    . "— pusher: " . ($automation->common_pusher_id ?? 'none')
                    . ", event: " . ($automation->event_name ?? 'none')
                    . " — {$row['status']}"
                );
            }
        }

        $this->info(
            "{$label}Breached items: " . count($report)
            . " | dispatched: {$counts['dispatched']}"
            . " | skipped (pending push): {$counts['skipped_pending']}"
            . ($dryRun ? " | would dispatch: {$counts['dry_run']}" : '')
        );

        return self::SUCCESS;
    }
}
