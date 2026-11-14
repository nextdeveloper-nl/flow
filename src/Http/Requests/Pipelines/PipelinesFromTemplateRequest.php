<?php

namespace NextDeveloper\Flow\Http\Requests\Pipelines;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;
use NextDeveloper\Flow\Services\PipelinesService;

/**
 * POST /flow/pipelines/from-template — a pipeline without a campaign (leo4#1241).
 * Give a template, a kind, or both; with only a kind the blank template of that kind is used.
 */
class PipelinesFromTemplateRequest extends AbstractFormRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'template_id' => 'required_without:kind|nullable|string|in:'.implode(',', array_keys(config('flow.pipeline_templates', []))),
            'kind' => 'required_without:template_id|nullable|string|in:'.implode(',', PipelinesService::KINDS),
            'name' => 'nullable|string|max:255',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'template_id.required_without' => 'Choose a template or a kind.',
            'template_id.in' => 'This pipeline template does not exist.',
            'kind.required_without' => 'Choose a template or a kind.',
            'kind.in' => 'The kind must be sales, marketing, project or custom.',
        ];
    }
}
