<?php

namespace NextDeveloper\Flow\Console\Commands;

use Illuminate\Console\Command;
use NextDeveloper\Flow\Services\ItemsService;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * One-off backfill for flow_items.sla_breached_at and sla_actioned_at, which were
 * added after items already existed. Run once after deploying; it is safe to
 * re-run (sla_breached_at is recalculated, sla_actioned_at is only filled where empty).
 *
 *   php artisan flow:backfill-sla-timestamps
 */
class BackfillSlaTimestampsCommand extends Command
{
    protected $signature = 'flow:backfill-sla-timestamps';

    protected $description = 'Backfill flow_items.sla_breached_at (from stage sla_days) and sla_actioned_at (from existing SLA pusher logs)';

    public function handle(): int
    {
        UserHelper::setAdminAsCurrentUser();

        $counts = ItemsService::backfillSlaTimestamps();

        $this->info("sla_breached_at set on {$counts['sla_breached_at']} item(s).");
        $this->info("sla_actioned_at set on {$counts['sla_actioned_at']} item(s) that were already notified in their current stage.");

        return self::SUCCESS;
    }
}
