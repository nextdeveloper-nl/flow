<?php

namespace NextDeveloper\Flow\Http\Requests\Pipelines;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class PipelinesUpdateRequest extends AbstractFormRequest
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
        'is_template' => 'boolean',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}