<?php

namespace NextDeveloper\Flow\Http\Requests\ItemValues;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class ItemValuesCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'flow_item_id' => 'required|exists:flow_items,uuid|uuid',
        'flow_column_id' => 'required|exists:flow_columns,uuid|uuid',
        'value' => 'nullable|string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}