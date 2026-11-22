<?php

namespace NextDeveloper\Flow\Http\Controllers\Higgsfield;

use NextDeveloper\Commons\Database\Models\Media;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\Flow\Http\Controllers\AbstractController;
use NextDeveloper\Flow\Http\Requests\Higgsfield\HiggsfieldGenerateRequest;
use NextDeveloper\Flow\Services\Higgsfield\HiggsfieldException;
use NextDeveloper\Flow\Services\HiggsfieldGenerationsService;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * On-demand Higgsfield image/video generation for a person (no Flow automation). Higgsfield is only ever reached
 * from here, never from the browser. No logic lives in this controller: validation, guards and the work are in
 * HiggsfieldGenerationsService; this only turns its refusals into the standard error responses.
 */
class HiggsfieldController extends AbstractController
{
    private $model = Media::class;

    /**
     * POST /flow/higgsfield/estimate - cost of a request, nothing is submitted or charged.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function estimate(HiggsfieldGenerateRequest $request)
    {
        try {
            $estimate = HiggsfieldGenerationsService::estimateOnDemand(
                (int) UserHelper::currentAccount()->id,
                $request->input('external_service_id'),
                (string) $request->input('model_path'),
                (string) $request->input('prompt'),
                (array) $request->input('params', [])
            );
        } catch (HiggsfieldException $e) {
            return $this->higgsfieldError($e);
        }

        return ResponsableFactory::makeResponse($this, $estimate);
    }

    /**
     * POST /flow/higgsfield/generate - submits a generation and returns its media row (status pending/submitted;
     * custom_properties.estimated_usd holds the cost). The file arrives asynchronously: poll the row until
     * custom_properties.status is completed (or failed / nsfw / canceled).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(HiggsfieldGenerateRequest $request)
    {
        try {
            $media = HiggsfieldGenerationsService::generateOnDemand(
                (int) UserHelper::currentAccount()->id,
                (int) UserHelper::me()->id,
                $request->input('external_service_id'),
                (string) $request->input('model_path'),
                (string) $request->input('media_type'),
                (string) $request->input('prompt'),
                (array) $request->input('params', []),
                $request->filled('max_cost_usd') ? (float) $request->input('max_cost_usd') : null
            );
        } catch (HiggsfieldException $e) {
            return $this->higgsfieldError($e);
        }

        return ResponsableFactory::makeResponse($this, $media);
    }

    /**
     * Maps a Higgsfield / guard refusal onto the standard error response of the right status.
     */
    private function higgsfieldError(HiggsfieldException $e)
    {
        return match (true) {
            $e->httpStatus === 403 => $this->errorForbidden($e->getMessage()),
            $e->httpStatus === 422 => $this->errorUnprocessable($e->getMessage()),
            $e->httpStatus === 429 => $this->errorToManyRequests($e->getMessage()),
            // Unreachable, 5xx, or our own platform key rejected (401): the upstream service failed, not the caller.
            $e->httpStatus === 0, $e->httpStatus === 401, $e->httpStatus >= 500 => $this->errorBadGateway($e->getMessage()),
            default => $this->errorBadRequest($e->getMessage()),
        };
    }
}
