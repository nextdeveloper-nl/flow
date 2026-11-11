<?php

namespace NextDeveloper\Flow\Services;

use NextDeveloper\Flow\Database\Models\Stages;
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
