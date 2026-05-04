<?php

namespace NextDeveloper\Flow\Http\Requests\ItemWatchers;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class ItemWatchersUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'flow_item_id' => 'nullable|exists:flow_items,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}