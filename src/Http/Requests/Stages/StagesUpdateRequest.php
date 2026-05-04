<?php

namespace NextDeveloper\Flow\Http\Requests\Stages;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class StagesUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'flow_pipeline_id' => 'nullable|exists:flow_pipelines,uuid|uuid',
        'name' => 'nullable|string',
        'color' => 'nullable|string',
        'position' => 'integer',
        'probability' => 'integer',
        'sla_days' => 'nullable|integer',
        'is_won' => 'boolean',
        'is_lost' => 'boolean',
        'checklist' => 'nullable',
        'is_active' => 'boolean',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}