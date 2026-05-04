<?php

namespace NextDeveloper\Flow\Http\Requests\StageHistories;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class StageHistoriesUpdateRequest extends AbstractFormRequest
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
        'moved_by_iam_user_id' => 'nullable|exists:iam_users,uuid|uuid',
        'moved_at' => 'date',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}