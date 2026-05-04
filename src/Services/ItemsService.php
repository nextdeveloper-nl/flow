<?php

namespace NextDeveloper\Flow\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use NextDeveloper\Flow\Services\AbstractServices\AbstractItemsService;
use NextDeveloper\Flow\Database\Models\Items;
use NextDeveloper\Flow\Database\Models\Stages;
use NextDeveloper\Flow\Database\Models\StageHistories;
use NextDeveloper\Flow\Database\Models\Automations;
use NextDeveloper\Flow\Database\Models\StageRequiredColumns;
use NextDeveloper\Flow\Database\Models\ItemValues;
use NextDeveloper\Flow\Database\Models\ItemWatchers;
use NextDeveloper\Commons\Helpers\DatabaseHelper;
use NextDeveloper\IAM\Helpers\UserHelper;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\Commons\Exceptions\NotAllowedException;

/**
 * This class is responsible from managing the data for Items
 *
 * Class ItemsService.
 *
 * @package NextDeveloper\Flow\Database\Models
 */
class ItemsService extends AbstractItemsService
{
    /**
     * Creates the item, records the initial history entry and fires item_created automations.
     */
    public static function create(array $data)
    {
        if (!empty($data['object_id']) && !empty($data['object_type']) && Str::isUuid($data['object_id'])) {
            $table = Str::plural($data['object_type']);
            $data['object_id'] = DB::table($table)->where('uuid', $data['object_id'])->value('id');
        }

        $item = parent::create($data);

        StageHistories::create([
            'flow_item_id'         => $item->id,
            'flow_pipeline_id'     => $item->flow_pipeline_id,
            'from_stage_id'        => null,
            'to_stage_id'          => $item->flow_stage_id,
            'moved_by_iam_user_id' => $item->iam_user_id,
            'moved_at'             => now(),
        ]);

        self::fireAutomations($item, null, $item->flow_stage_id, 'item_created');

        return $item;
    }

    /**
     * Updates the item. When flow_stage_id changes, validates required columns,
     * resets the checklist, records history, fires automations and notifies watchers.
     */
    public static function update($id, array $data)
    {
        $item = Items::where('uuid', $id)->first();

        if (!$item) {
            throw new NotAllowedException(
                'We cannot find the related object to update. ' .
                'Maybe you dont have the permission to update this object?'
            );
        }

        $isStageMove = false;
        $oldStageId  = $item->flow_stage_id;
        $newStageId  = null;

        if (array_key_exists('flow_stage_id', $data)) {
            $newStageId = DatabaseHelper::uuidToId(
                '\NextDeveloper\Flow\Database\Models\Stages',
                $data['flow_stage_id']
            );

            if ($newStageId !== $oldStageId) {
                self::validateStageEntry($item, $newStageId);

                $isStageMove                   = true;
                $data['checklist_state']       = null;
                $data['last_stage_changed_at'] = now();
            }
        }

        $model = parent::update($id, $data);

        if ($isStageMove) {
            StageHistories::create([
                'flow_item_id'         => $model->id,
                'flow_pipeline_id'     => $model->flow_pipeline_id,
                'from_stage_id'        => $oldStageId,
                'to_stage_id'          => $newStageId,
                'moved_by_iam_user_id' => UserHelper::me()->id,
                'moved_at'             => now(),
            ]);

            self::fireAutomations($model, $oldStageId, $newStageId, 'stage_exited');
            self::fireAutomations($model, $oldStageId, $newStageId, 'stage_entered');
            self::notifyWatchers($model);
        }

        return $model;
    }

    /**
     * Marks a single checklist key as complete for the given item.
     */
    public static function completeChecklistItem(string $itemUuid, string $key): Items
    {
        $item  = Items::where('uuid', $itemUuid)->firstOrFail();
        $state = $item->checklist_state ?? [];

        $state[$key] = [
            'completed'    => true,
            'completed_by' => UserHelper::me()->id,
            'completed_at' => now()->toIso8601String(),
        ];

        $item->update(['checklist_state' => $state]);

        return $item->fresh();
    }

    /**
     * Marks a single checklist key as incomplete for the given item.
     */
    public static function uncompleteChecklistItem(string $itemUuid, string $key): Items
    {
        $item  = Items::where('uuid', $itemUuid)->firstOrFail();
        $state = $item->checklist_state ?? [];

        $state[$key] = [
            'completed'    => false,
            'completed_by' => null,
            'completed_at' => null,
        ];

        $item->update(['checklist_state' => $state]);

        return $item->fresh();
    }

    /**
     * Validates that all required columns for the target stage have values on this item.
     *
     * @throws NotAllowedException if any required column value is missing.
     */
    private static function validateStageEntry(Items $item, int $newStageId): void
    {
        $requiredColumnIds = StageRequiredColumns::where('flow_stage_id', $newStageId)
            ->pluck('flow_column_id');

        if ($requiredColumnIds->isEmpty()) {
            return;
        }

        $filledColumnIds = ItemValues::where('flow_item_id', $item->id)
            ->whereIn('flow_column_id', $requiredColumnIds)
            ->whereNotNull('value')
            ->pluck('flow_column_id');

        $missing = $requiredColumnIds->diff($filledColumnIds);

        if ($missing->isNotEmpty()) {
            throw new NotAllowedException(
                'Cannot move to this stage. Missing required column values for column IDs: ' .
                $missing->implode(', ')
            );
        }
    }

    /**
     * Queries active automations for the given trigger and fires each one via the Events system.
     */
    private static function fireAutomations(Items $item, ?int $fromStageId, ?int $toStageId, string $trigger): void
    {
        $query = Automations::where('flow_pipeline_id', $item->flow_pipeline_id)
            ->where('is_active', true)
            ->where('trigger', $trigger);

        if ($trigger === 'stage_entered') {
            $query->where(function ($q) use ($toStageId) {
                $q->whereNull('flow_stage_id')
                  ->orWhere('flow_stage_id', $toStageId);
            });
        } elseif ($trigger === 'stage_exited') {
            $query->where(function ($q) use ($fromStageId) {
                $q->whereNull('flow_stage_id')
                  ->orWhere('flow_stage_id', $fromStageId);
            });
        }

        foreach ($query->get() as $automation) {
            Events::fire($automation->event_name, $item);
        }
    }

    /**
     * Fires a stage-change event so registered listeners can notify each watcher.
     */
    private static function notifyWatchers(Items $item): void
    {
        $hasWatchers = ItemWatchers::where('flow_item_id', $item->id)->exists();

        if (!$hasWatchers) {
            return;
        }

        Events::fire('item_stage_changed:NextDeveloper\Flow\Items', $item);
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
