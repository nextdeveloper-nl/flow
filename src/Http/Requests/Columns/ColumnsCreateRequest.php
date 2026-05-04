<?php

namespace NextDeveloper\Flow\Http\Requests\Columns;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class ColumnsCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'flow_pipeline_id' => 'required|exists:flow_pipelines,uuid|uuid',
        'name' => 'required|string',
        'label' => 'required|string',
        'field_type' => 'string',
        'options' => 'nullable',
        'default_value' => 'nullable|string',
        'is_required' => 'boolean',
        'position' => 'integer',
        'is_active' => 'boolean',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}