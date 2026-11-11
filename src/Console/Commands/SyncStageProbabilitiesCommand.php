<?php

namespace NextDeveloper\Flow\Console\Commands;

use Illuminate\Console\Command;
use NextDeveloper\Flow\Database\Models\Stages;
use NextDeveloper\Flow\Services\ItemsService;
use NextDeveloper\Flow\Services\PipelinesService;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * One-off backfill: copies every stage's probability onto the linked objects
 * (e.g. CRM opportunities) of the items currently in it. Objects whose probability
 * is not the 0 default are left alone, so it is safe to re-run.
 *
 *   php artisan flow:sync-stage-probabilities
 */
class SyncStageProbabilitiesCommand extends Command
{
    protected $signature = 'flow:sync-stage-probabilities';

    protected $description = 'Copy each pipeline stage probability onto the linked objects (opportunities) still on the 0 default';

    public function handle(): int
    {
        UserHelper::setAdminAsCurrentUser();

        $total = 0;

        Stages::withoutGlobalScopes()
            ->whereNull('deleted_at')
            ->where('probability', '>', 0)
            ->orderBy('id')
            ->each(function (Stages $stage) use (&$total) {
                $updated = ItemsService::syncObjectProbabilityForStage($stage);

                if ($updated > 0) {
                    PipelinesService::invalidateBoardByPipelineId($stage->flow_pipeline_id);
                    $this->line("Stage #{$stage->id} ({$stage->name}, {$stage->probability}%): {$updated} object(s) updated.");
                }

                $total += $updated;
            });

        $this->info("Probability set on {$total} object(s).");

        return self::SUCCESS;
    }
}
