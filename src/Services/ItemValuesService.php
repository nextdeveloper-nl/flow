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
}
