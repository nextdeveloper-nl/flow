<?php

namespace NextDeveloper\Flow\Http\Requests\Stages;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

/**
 * POST /flow/stages/{id}/required-columns — the fields a card needs before entering the stage (leo4#1243).
 */
class StagesRequiredColumnsRequest extends AbstractFormRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'flow_column_ids' => 'present|array',
            'flow_column_ids.*' => 'uuid',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'flow_column_ids.present' => 'Send the list of required fields, even when it is empty.',
            'flow_column_ids.*.uuid' => 'One of the fields is not valid.',
        ];
    }
}
