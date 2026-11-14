<?php

namespace NextDeveloper\Flow\Http\Requests\Items;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

/**
 * POST /flow/items/{id}/link — attach a record to a card (leo4#1243).
 */
class ItemsLinkRequest extends AbstractFormRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            // Short form, e.g. NextDeveloper\CRM\Opportunities.
            'object_type' => 'required|string|max:255',
            'object_id' => 'required|uuid',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'object_type.required' => 'Choose what kind of record to link.',
            'object_id.required' => 'Choose the record to link.',
        ];
    }
}
