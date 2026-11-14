<?php

namespace NextDeveloper\Flow\Services;

use NextDeveloper\Flow\Services\AbstractServices\AbstractItemValuesService;
use NextDeveloper\Flow\Database\Models\ItemValues;
use NextDeveloper\Commons\Helpers\DatabaseHelper;

/**
 * This class is responsible from managing the data for ItemValues
 *
 * Class ItemValuesService.
 *
 * @package NextDeveloper\Flow\Database\Models
 */
class ItemValuesService extends AbstractItemValuesService
{
    /**
     * Inserts or updates a column value for an item (upsert on flow_item_id + flow_column_id).
     */
    public static function upsert(string $itemUuid, string $columnUuid, string $value): ItemValues
    {
        $itemId   = DatabaseHelper::uuidToId('\NextDeveloper\Flow\Database\Models\Items', $itemUuid);
        $columnId = DatabaseHelper::uuidToId('\NextDeveloper\Flow\Database\Models\Columns', $columnUuid);

        return ItemValues::updateOrCreate(
            ['flow_item_id' => $itemId, 'flow_column_id' => $columnId],
            ['value' => $value]
        );
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    /**
     * One value per card and field (UNIQUE flow_item_id, flow_column_id): saving a field again
     * updates it instead of failing on the unique key. The value is checked against the column
     * (leo4#1243), and an empty value removes it.
     */
    public static function create(array $data)
    {
        $item = \NextDeveloper\Flow\Database\Models\Items::where('uuid', $data['flow_item_id'] ?? null)->first();
        $column = \NextDeveloper\Flow\Database\Models\Columns::withoutGlobalScopes()->where('uuid', $data['flow_column_id'] ?? null)->first();

        if (!$item || !$column || $column->flow_pipeline_id !== $item->flow_pipeline_id) {
            throw new \NextDeveloper\Commons\Exceptions\NotAllowedException('This field does not belong to the card\'s pipeline.');
        }

        return self::saveValue($item, $column, $data['value'] ?? null);
    }

    public static function saveValue(\NextDeveloper\Flow\Database\Models\Items $item, \NextDeveloper\Flow\Database\Models\Columns $column, mixed $value): ?ItemValues
    {
        $normalized = \NextDeveloper\Flow\Helpers\ColumnValues::normalize($column, $value);

        $existing = ItemValues::withoutGlobalScopes()
            ->where('flow_item_id', $item->id)
            ->where('flow_column_id', $column->id)
            ->first();

        if ($normalized === null) {
            $existing?->delete();
            \NextDeveloper\Flow\Services\PipelinesService::invalidateBoardByPipelineId($item->flow_pipeline_id);

            return null;
        }

        if ($existing) {
            $existing->update(['value' => $normalized]);
        } else {
            $existing = ItemValues::create([
                'flow_item_id'   => $item->id,
                'flow_column_id' => $column->id,
                'value'          => $normalized,
            ]);
        }

        \NextDeveloper\Flow\Services\PipelinesService::invalidateBoardByPipelineId($item->flow_pipeline_id);

        return $existing->fresh();
    }
}
