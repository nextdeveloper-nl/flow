<?php

namespace NextDeveloper\Flow\Http\Requests\StagesPerspective;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class StagesPerspectiveCreateRequest extends AbstractFormRequest
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
        'position' => 'nullable|integer',
        'probability' => 'nullable|integer',
        'sla_days' => 'nullable|integer',
        'is_won' => 'nullable|boolean',
        'is_lost' => 'nullable|boolean',
        'checklist' => 'nullable',
        'is_active' => 'nullable|boolean',
        'pipeline_name' => 'nullable|string',
        'pipeline_object_type' => 'nullable|string',
        'pipeline_iam_account_id' => 'nullable|exists:pipeline_iam_accounts,uuid|uuid',
        'item_count' => 'nullable|integer',
        'sla_breached_count' => 'nullable|integer',
        'avg_days_in_stage' => 'nullable',
        'required_column_count' => 'nullable|integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}