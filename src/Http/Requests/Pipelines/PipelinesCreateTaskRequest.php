<?php

namespace NextDeveloper\Flow\Http\Requests\Pipelines;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

/**
 * POST /flow/pipelines/{id}/tasks — a task card on a project board (leo4#1242).
 */
class PipelinesCreateTaskRequest extends AbstractFormRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            // 1 = low, 2 = medium, 3 = high.
            'priority' => 'nullable|integer|between:1,3',
            'assignee_id' => 'nullable|uuid|exists:iam_users,uuid',
            'crm_account_id' => 'nullable|uuid|exists:crm_accounts,uuid',
            'flow_stage_id' => 'nullable|uuid|exists:flow_stages,uuid',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Give the task a name.',
            'due_date.date' => 'The due date is not a valid date.',
            'priority.between' => 'The priority must be 1 (low), 2 (medium) or 3 (high).',
        ];
    }
}
