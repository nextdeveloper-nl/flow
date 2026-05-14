<?php

namespace NextDeveloper\Flow\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\Flow\Services\AbstractServices\AbstractItemsService;
use NextDeveloper\Flow\Database\Models\Items;
use NextDeveloper\Flow\Database\Models\StageHistories;
use NextDeveloper\Flow\Database\Models\Stages;
use NextDeveloper\Flow\Database\Models\Automations;
use NextDeveloper\Flow\Database\Models\StageRequiredColumns;
use NextDeveloper\Flow\Database\Models\ItemValues;
use NextDeveloper\Flow\Database\Models\ItemWatchers;
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

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

        /**
     * Creates the item, records the initial history entry and fires item_created automations.
     */
    public static function create(array $data)
    {
        if (!empty($data['object_id']) && !empty($data['object_type']) && Str::isUuid($data['object_id'])) {
            $data['object_id'] = self::resolveObjectId($data['object_type'], $data['object_id']);
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
            $stage = Stages::withoutGlobalScopes()
                ->where('uuid', $data['flow_stage_id'])
                ->first();

            if (!$stage) {
                throw new NotAllowedException('Stage not found: ' . $data['flow_stage_id']);
            }

            $newStageId = $stage->id;
            // Replace UUID with the resolved integer so parent::update() skips re-conversion
            $data['flow_stage_id'] = $newStageId;

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
            self::fireAutomations($model, $oldStageId, $newStageId, 'stage_left');
            self::fireAutomations($model, $oldStageId, $newStageId, 'stage_entered');
            self::fireAutomations($model, $oldStageId, $newStageId, 'item_moved');
            self::notifyWatchers($model);
        }

        return $model;
    }

    public static function delete($id)
    {
        $item = Items::withoutGlobalScopes()->where('uuid', $id)->first();

        if ($item) {
            self::fireAutomations($item, $item->flow_stage_id, null, 'item_deleted');
        }

        return parent::delete($id);
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
        $requiredColumnIds = StageRequiredColumns::withoutGlobalScopes()
            ->where('flow_stage_id', $newStageId)
            ->pluck('flow_column_id');

        if ($requiredColumnIds->isEmpty()) {
            return;
        }

        $filledColumnIds = ItemValues::withoutGlobalScopes()
            ->where('flow_item_id', $item->id)
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
        $query = Automations::withoutGlobalScopes()
            ->where('flow_pipeline_id', $item->flow_pipeline_id)
            ->where('is_active', true)
            ->where('trigger', $trigger);

        if ($trigger === 'stage_entered') {
            $query->where(function ($q) use ($toStageId) {
                $q->whereNull('flow_stage_id')
                  ->orWhere('flow_stage_id', $toStageId);
            });
        } elseif ($trigger === 'stage_exited' || $trigger === 'stage_left') {
            $query->where(function ($q) use ($fromStageId) {
                $q->whereNull('flow_stage_id')
                  ->orWhere('flow_stage_id', $fromStageId);
            });
        }
        // item_moved, item_created, item_deleted have no stage filter

        foreach ($query->get() as $automation) {
            if ($automation->common_pusher_id) {
                self::triggerPusher($automation, $item);
            }

            if (!$automation->event_name) {
                continue;
            }

            Events::fire($automation->event_name, $item);
        }
    }

    /**
     * Fires a stage-change event so registered listeners can notify each watcher.
     */
    private static function notifyWatchers(Items $item): void
    {
        $hasWatchers = ItemWatchers::withoutGlobalScopes()->where('flow_item_id', $item->id)->exists();

        if (!$hasWatchers) {
            return;
        }

        Events::fire('item_stage_changed:NextDeveloper\Flow\Items', $item);
    }

    public static function triggerPusherForAutomation(Automations $automation, Items $item): void
    {
        self::triggerPusher($automation, $item);
    }

    private static function triggerPusher(Automations $automation, Items $item): void
    {
        $pusher = \NextDeveloper\Commons\Database\Models\Pushers::withoutGlobalScopes()
            ->where('id', $automation->common_pusher_id)
            ->first();

        if (!$pusher || !$pusher->url) {
            return;
        }

        $method  = strtolower($pusher->method ?? 'post');

        $object = self::resolveObject($item->object_type, $item->object_id);

        $payload = array_merge($automation->payload_template ?? [], [
            'flow_item_id'   => $item->uuid,
            'object_type'    => $item->object_type,
            'object_id'      => $item->object_id,
            'flow_stage_id'  => $item->flow_stage_id,
            'object'         => $object ? self::transformObject($object) : null,
        ]);

        $client = Http::acceptJson();

        if ($pusher->require_auth && $pusher->token && $pusher->auth_header) {
            $client = $client->withHeaders([$pusher->auth_header => $pusher->token]);
        }

        try {
            $client->$method($pusher->url, $payload);
        } catch (\Throwable $e) {
            Log::warning('[Flow] Pusher trigger failed for automation ' . $automation->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Transforms a model through its corresponding HTTP transformer if one exists.
     * Falls back to toArray() when no transformer class can be found.
     */
    private static function transformObject(object $object): array
    {
        $modelClass       = get_class($object);
        $transformerClass = str_replace('\\Database\\Models\\', '\\Http\\Transformers\\', $modelClass) . 'Transformer';

        if (class_exists($transformerClass)) {
            try {
                return (new $transformerClass())->transform($object);
            } catch (\Throwable $e) {
                Log::warning('[Flow] Transformer failed for ' . $modelClass . ': ' . $e->getMessage());
            }
        }

        return $object->toArray();
    }

    private static function resolveObject(string $objectType, int $id): ?object
    {
        $parts      = array_values(array_filter(explode('\\', $objectType)));
        $className  = array_pop($parts);
        $modelClass = '\\' . implode('\\', $parts) . '\\Database\\Models\\' . $className;

        if (!class_exists($modelClass)) {
            return null;
        }

        return $modelClass::withoutGlobalScopes()->where('id', $id)->first();
    }

    /**
     * Converts a short namespace (e.g. \NextDeveloper\CRM\Opportunities) and a UUID
     * into the integer primary key by routing through the full model class so that
     * authorization scopes are enforced and arbitrary table access is prevented.
     */
    private static function resolveObjectId(string $objectType, string $uuid): int
    {
        $parts      = array_values(array_filter(explode('\\', $objectType)));
        $className  = array_pop($parts);
        $modelClass = '\\' . implode('\\', $parts) . '\\Database\\Models\\' . $className;

        if (!class_exists($modelClass)) {
            throw new NotAllowedException('Invalid object_type: ' . $objectType);
        }

        $object = $modelClass::where('uuid', $uuid)->first();

        if (!$object) {
            throw new NotAllowedException(
                'Cannot find or access the referenced object. ' .
                'Check that the object exists and that you have permission to use it.'
            );
        }

        return $object->id;
    }
}
