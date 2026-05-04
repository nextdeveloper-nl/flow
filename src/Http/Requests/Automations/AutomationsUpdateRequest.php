<?php

namespace NextDeveloper\Flow\Http\Requests\Automations;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class AutomationsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'flow_pipeline_id' => 'nullable|exists:flow_pipelines,uuid|uuid',
        'flow_stage_id' => 'nullable|exists:flow_stages,uuid|uuid',
        'trigger' => 'nullable|string',
        'event_name' => 'nullable|string',
        'payload_template' => 'nullable',
        'is_active' => 'boolean',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}