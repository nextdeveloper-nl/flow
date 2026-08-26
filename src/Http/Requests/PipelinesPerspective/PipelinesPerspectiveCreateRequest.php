<?php

namespace NextDeveloper\Flow\Http\Requests\PipelinesPerspective;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class PipelinesPerspectiveCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'description' => 'nullable|string',
        'object_type' => 'nullable|string',
        'object_id' => 'nullable|integer',
        'is_template' => 'nullable|boolean',
        'is_system' => 'nullable|boolean',
        'is_active' => 'nullable|boolean',
        'stage_count' => 'nullable|integer',
        'active_item_count' => 'nullable|integer',
        'won_item_count' => 'nullable|integer',
        'lost_item_count' => 'nullable|integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}