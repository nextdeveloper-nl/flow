<?php

namespace NextDeveloper\Flow\Http\Requests\Items;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class ItemsCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'flow_pipeline_id' => 'required|exists:flow_pipelines,uuid|uuid',
        'flow_stage_id' => 'required|exists:flow_stages,uuid|uuid',
        'object_type' => 'required|string',
        'object_id' => 'required',
        'position' => 'integer',
        'last_stage_changed_at' => 'date',
        'checklist_state' => 'nullable',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}