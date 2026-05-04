<?php

namespace NextDeveloper\Flow\Http\Requests\StageHistoryPerspective;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class StageHistoryPerspectiveUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'flow_item_id' => 'nullable|exists:flow_items,uuid|uuid',
        'flow_pipeline_id' => 'nullable|exists:flow_pipelines,uuid|uuid',
        'from_stage_id' => 'nullable|exists:from_stages,uuid|uuid',
        'to_stage_id' => 'nullable|exists:to_stages,uuid|uuid',
        'moved_by_iam_user_id' => 'nullable|exists:moved_by_iam_users,uuid|uuid',
        'moved_at' => 'nullable|date',
        'from_stage_name' => 'nullable|string',
        'from_stage_color' => 'nullable|string',
        'to_stage_name' => 'nullable|string',
        'to_stage_color' => 'nullable|string',
        'moved_by_name' => 'nullable|string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}