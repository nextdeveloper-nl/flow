<?php

namespace NextDeveloper\Flow\Http\Requests\Higgsfield;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

/**
 * Body of the on-demand Higgsfield endpoints (estimate and generate). The model path itself is checked in
 * HiggsfieldClient::cleanPath (plain relative path on our fixed base URL), params are size-bounded in the service.
 */
class HiggsfieldGenerateRequest extends AbstractFormRequest
{
    /**
     * @return array
     */
    public function rules()
    {
        return [
            'model_path'          => 'required|string|max:200',
            'media_type'          => 'required|string|in:image,video',
            'prompt'              => 'required|string|max:4000',
            'params'              => 'nullable|array',
            // Uuid of the account's own Higgsfield credentials. Empty = platform credentials (allow-listed accounts only).
            'external_service_id' => 'nullable|uuid',
            // Optional ceiling for this one generation, in USD. It can only lower the platform ceiling.
            'max_cost_usd'        => 'nullable|numeric|min:0',
        ];
    }
}
