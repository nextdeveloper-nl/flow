<?php

namespace NextDeveloper\Flow\Http\Requests\ItemsPerspective;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class ItemsPerspectiveUpdateRequest extends AbstractFormRequest
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
        'assigned_iam_user_id' => 'nullable|exists:assigned_iam_users,uuid|uuid',
        'stage_name' => 'nullable|string',
        'stage_color' => 'nullable|string',
        'stage_sla_days' => 'nullable|integer',
        'object_name' => 'nullable|string',
        'object_subtitle' => 'nullable|string',
        'object_value' => 'nullable',
        'sla_breached' => 'nullable|boolean',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}