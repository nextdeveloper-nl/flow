<?php

namespace NextDeveloper\Flow\Services;

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

        $model = parent::update($id, $data);

        if ($affectsSla) {
            ItemsService::recalculateSlaBreachedAtForStage($model);
        }

        return $model;
    }
}
