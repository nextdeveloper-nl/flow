<?php

namespace NextDeveloper\Flow\Console\Commands;

use Illuminate\Console\Command;
use NextDeveloper\Flow\Jobs\CheckSlaBreachesJob;

/**
 * Manually runs the SLA breach check for testing purposes.
 *
 * Examples:
 *   php artisan flow:check-sla-breaches          (dispatches to queue)
 *   php artisan flow:check-sla-breaches --sync   (runs inline, output visible immediately)
 */
class CheckSlaBreachesCommand extends Command
{
    protected $signature = 'flow:check-sla-breaches
        {--sync : Run synchronously in this process instead of dispatching to the queue}';

    protected $description = 'Check for SLA breaches across all active flow items and fire sla_breached automations';

    public function handle(): int
    {
        if ($this->option('sync')) {
            $this->info('Running SLA breach check synchronously...');
            (new CheckSlaBreachesJob())->handle();
            $this->info('Done.');
        } else {
            CheckSlaBreachesJob::dispatch();
            $this->info('SLA breach check job dispatched to the queue.');
        }

        return self::SUCCESS;
    }
}
