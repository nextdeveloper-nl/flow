<?php

namespace NextDeveloper\Flow\Http\Requests\ItemValuesPerspective;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class ItemValuesPerspectiveUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'flow_pipeline_id' => 'nullable|exists:flow_pipelines,uuid|uuid',
        'flow_stage_id' => 'nullable|exists:flow_stages,uuid|uuid',
        'object_type' => 'nullable|string',
        'object_id' => 'nullable',
        'position' => 'nullable|integer',
        'last_stage_changed_at' => 'nullable|date',
        'checklist_state' => 'nullable',
        'stage_name' => 'nullable|string',
        'stage_color' => 'nullable|string',
        'pipeline_name' => 'nullable|string',
        'pipeline_object_type' => 'nullable|string',
        'field_values' => 'nullable',
        'field_types' => 'nullable',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}