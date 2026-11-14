<?php

namespace NextDeveloper\Flow\Services;

use Illuminate\Support\Facades\DB;
use NextDeveloper\Commons\Exceptions\NotAllowedException;
use NextDeveloper\Flow\Database\Models\Columns;
use NextDeveloper\Flow\Database\Models\StageRequiredColumns;
use NextDeveloper\Flow\Database\Models\Stages;
use NextDeveloper\IAM\Helpers\UserHelper;
use NextDeveloper\Flow\Services\AbstractServices\AbstractStagesService;

/**
 * This class is responsible from managing the data for Stages
 *
 * Class StagesService.
 *
 * @package NextDeveloper\Flow\Database\Models
 */
class StagesService extends AbstractStagesService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    /**
     * The fields a card must have filled before it can enter this stage (leo4#1243).
     *
     * @return array<int, string> Column uuids.
     */
    public static function requiredColumnUuids(string $stageUuid): array
    {
        $stage = self::findStageOrFail($stageUuid);

        return Columns::withoutGlobalScopes()
            ->whereIn('id', StageRequiredColumns::withoutGlobalScopes()->where('flow_stage_id', $stage->id)->select('flow_column_id'))
            ->orderBy('position')
            ->pluck('uuid')
            ->all();
    }

    /**
     * Replaces the stage's required fields with the given columns of the same pipeline (leo4#1243).
     *
     * @param  array<int, string>  $columnUuids
     * @return array<int, string> Column uuids now required.
     */
    public static function syncRequiredColumns(string $stageUuid, array $columnUuids): array
    {
        $stage = self::findStageOrFail($stageUuid);

        throw_if(
            !UserHelper::can('update', $stage),
            new NotAllowedException('You are not allowed to change this stage.')
        );

        $columnIds = Columns::withoutGlobalScopes()
            ->where('flow_pipeline_id', $stage->flow_pipeline_id)
            ->whereIn('uuid', array_values(array_unique($columnUuids)))
            ->pluck('id');

        if ($columnIds->count() !== count(array_unique($columnUuids))) {
            throw new NotAllowedException('Some of these fields do not belong to this pipeline.');
        }

        DB::transaction(function () use ($stage, $columnIds) {
            StageRequiredColumns::withoutGlobalScopes()
                ->where('flow_stage_id', $stage->id)
                ->whereNotIn('flow_column_id', $columnIds)
                ->delete();

            $existing = StageRequiredColumns::withoutGlobalScopes()
                ->where('flow_stage_id', $stage->id)
                ->pluck('flow_column_id');

            foreach ($columnIds->diff($existing) as $columnId) {
                StageRequiredColumns::create(['flow_stage_id' => $stage->id, 'flow_column_id' => $columnId]);
            }
        });

        PipelinesService::invalidateBoardByPipelineId($stage->flow_pipeline_id);

        return self::requiredColumnUuids($stageUuid);
    }

    private static function findStageOrFail(string $stageUuid): Stages
    {
        $stage = Stages::where('uuid', $stageUuid)->first();

        if (!$stage) {
            throw new NotAllowedException('We cannot find this stage.');
        }

        return $stage;
    }

    /**
     * Updates the stage. flow_items.sla_breached_at is stored per item at stage
     * entry (entered + sla_days), so when the stage's SLA rules change — sla_days,
     * or it becomes a won/lost stage — every item currently in it gets its deadline
     * recalculated.
     */
    public static function update($id, array $data)
    {
        $slaFields = ['sla_days', 'is_won', 'is_lost'];
        $affectsSla = (bool) array_intersect($slaFields, array_keys($data));

        $previousProbability = array_key_exists('probability', $data)
            ? Stages::withoutGlobalScopes()->where('uuid', $id)->value('probability')
            : null;
        $previousProbability = $previousProbability === null ? null : (int) $previousProbability;

        $model = parent::update($id, $data);

        if ($affectsSla) {
            ItemsService::recalculateSlaBreachedAtForStage($model);
        }

        if (array_key_exists('probability', $data) && $previousProbability !== $model->probability) {
            ItemsService::syncObjectProbabilityForStage($model, $previousProbability);
        }

        // Stage name/color/position/SLA changes alter the board: drop the cached board snapshots.
        PipelinesService::invalidateBoardByPipelineId($model->flow_pipeline_id);

        return $model;
    }

    /**
     * Creates the stage and drops the cached board snapshots of its pipeline.
     */
    public static function create(array $data)
    {
        $model = parent::create($data);

        PipelinesService::invalidateBoardByPipelineId($model->flow_pipeline_id);

        return $model;
    }

    /**
     * Deletes the stage and drops the cached board snapshots of its pipeline.
     */
    public static function delete($id)
    {
        $stage = Stages::withoutGlobalScopes()->where('uuid', $id)->first();

        $result = parent::delete($id);

        if ($stage) {
            PipelinesService::invalidateBoardByPipelineId($stage->flow_pipeline_id);
        }

        return $result;
    }
}
