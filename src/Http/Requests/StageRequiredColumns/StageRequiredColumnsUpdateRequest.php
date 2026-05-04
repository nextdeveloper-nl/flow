<?php

namespace NextDeveloper\Flow\Http\Requests\StageRequiredColumns;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class StageRequiredColumnsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'flow_stage_id' => 'nullable|exists:flow_stages,uuid|uuid',
        'flow_column_id' => 'nullable|exists:flow_columns,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}