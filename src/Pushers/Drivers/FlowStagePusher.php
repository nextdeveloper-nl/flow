<?php

namespace NextDeveloper\Flow\Pushers\Drivers;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Database\Models\PusherLogs;
use NextDeveloper\Commons\Database\Models\Pushers;
use NextDeveloper\Commons\Pushers\AbstractPusher;
use NextDeveloper\Commons\Pushers\PusherResult;
use NextDeveloper\Commons\Exceptions\NotAllowedException;
use NextDeveloper\Flow\Services\ItemsService;

/**
 * Moves a Flow item to a target stage. This pusher is exclusively for
 * Flow\Items — it does not interact with any other object in the project.
 *
 * Provider key: flow_stage
 *
 * Expected payload keys:
 *   flow_item_id  — UUID of the flow item to move (or falls back to body 'id')
 *
 * Required provider_metadata keys:
 *   flow_stage_id — UUID of the destination stage
 */
class FlowStagePusher extends AbstractPusher
{
    public static function provider(): string
    {
        return 'flow_stage';
    }

    public function send(PusherLogs $log, Pushers $pusher): PusherResult
    {
        if (!$this->isWithinLimit($pusher)) {
            $limit = $pusher->provider_metadata['hourly_limit'] ?? 10;

            Log::warning('[FlowStagePusher] Hourly push limit reached — skipping.', [
                'pusher_log_id' => $log->id,
                'pusher_id'     => $pusher->id,
                'hourly_limit'  => $limit,
            ]);

            return PusherResult::fail(429, 'Hourly push limit of ' . $limit . ' reached. Skipping.');
        }

        $body = $this->decodeBody($log);

        // Accept flow_item_id explicitly or fall back to the item's own uuid
        // when the pusher is fired directly from a Flow item automation.
        $itemUuid  = $body['flow_item_id'] ?? $body['id'] ?? null;

        // Destination stage is always configured on the pusher, never from the payload.
        $stageUuid = $pusher->provider_metadata['flow_stage_id'] ?? null;

        if (empty($itemUuid) || empty($stageUuid)) {
            Log::warning('[FlowStagePusher] Missing flow_item_id or flow_stage_id — cannot update stage.', [
                'pusher_log_id' => $log->id,
                'flow_item_id'  => $itemUuid,
                'flow_stage_id' => $stageUuid,
            ]);

            return PusherResult::fail(422, 'Missing flow_item_id or flow_stage_id in payload.');
        }

        Log::info('[FlowStagePusher] Updating flow item stage.', [
            'pusher_log_id' => $log->id,
            'flow_item_id'  => $itemUuid,
            'flow_stage_id' => $stageUuid,
        ]);

        try {
            ItemsService::update($itemUuid, [
                'flow_stage_id' => $stageUuid,
            ]);

            Log::info('[FlowStagePusher] Stage updated successfully.', [
                'pusher_log_id' => $log->id,
                'flow_item_id'  => $itemUuid,
                'flow_stage_id' => $stageUuid,
            ]);

            return PusherResult::ok(200, 'Stage updated for flow item: ' . $itemUuid);
        } catch (NotAllowedException $e) {
            Log::warning('[FlowStagePusher] Flow item not found or not accessible.', [
                'pusher_log_id' => $log->id,
                'flow_item_id'  => $itemUuid,
            ]);

            return PusherResult::fail(404, 'Flow item not found: ' . $itemUuid);
        } catch (\Throwable $e) {
            Log::error('[FlowStagePusher] Failed to update flow item stage.', [
                'pusher_log_id' => $log->id,
                'flow_item_id'  => $itemUuid,
                'flow_stage_id' => $stageUuid,
                'error'         => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
            ]);

            return PusherResult::fail(500, 'Failed to update stage: ' . $e->getMessage());
        }
    }

    private function isWithinLimit(Pushers $pusher): bool
    {
        $limit = (int) ($pusher->provider_metadata['hourly_limit'] ?? 10);

        $recentCount = PusherLogs::withoutGlobalScopes()
            ->where('common_pusher_id', $pusher->id)
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subHour())
            ->count();

        return $recentCount < $limit;
    }
}
