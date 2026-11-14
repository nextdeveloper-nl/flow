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
        // Optional on custom pipelines (leo4#1243): a card can have just a title and field values.
        'object_type' => 'required_with:object_id|nullable|string',
        'object_id' => 'required_with:object_type|nullable',
        'title' => 'required_without:object_id|nullable|string|max:255',
        'values' => 'sometimes|array',
        'values.*.flow_column_id' => 'required|uuid|exists:flow_columns,uuid',
        'values.*.value' => 'nullable',
        'position' => 'integer',
        'last_stage_changed_at' => 'date',
        'checklist_state' => 'nullable',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}