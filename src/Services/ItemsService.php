<?php

namespace NextDeveloper\Flow\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Database\Models\PusherLogs;
use NextDeveloper\Commons\Database\Models\Pushers;
use NextDeveloper\Commons\Exceptions\NotAllowedException;
use NextDeveloper\Commons\Services\PushersService;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\Flow\Database\Models\Automations;
use NextDeveloper\Flow\Database\Models\ItemValues;
use NextDeveloper\Flow\Database\Models\ItemWatchers;
use NextDeveloper\Flow\Database\Models\Items;
use NextDeveloper\Flow\Database\Models\ItemsPerspective;
use NextDeveloper\Flow\Database\Models\StageHistories;
use NextDeveloper\Flow\Database\Models\StageRequiredColumns;
use NextDeveloper\Flow\Database\Models\Stages;
use NextDeveloper\Flow\Jobs\TriggerSlaBreachAutomationJob;
use NextDeveloper\Flow\Services\AbstractServices\AbstractItemsService;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * This class is responsible from managing the data for Items
 *
 * Class ItemsService.
 *
 * @package NextDeveloper\Flow\Database\Models
 */
class ItemsService extends AbstractItemsService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

        /**
     * Creates the item, records the initial history entry and fires item_created automations.
     */
    public static function create(array $data)
    {
        if (!empty($data['object_id']) && !empty($data['object_type']) && Str::isUuid($data['object_id'])) {
            $data['object_id'] = self::resolveObjectId($data['object_type'], $data['object_id']);
        }

        $item = parent::create($data);

        // Store when the item's SLA in its first stage runs out (see calculateSlaBreachedAt).
        $slaBreachedAt = self::calculateSlaBreachedAt(
            Stages::withoutGlobalScopes()->where('id', $item->flow_stage_id)->first(),
            $item->last_stage_changed_at ?? $item->created_at
        );

        if ($slaBreachedAt) {
            $item = parent::update($item->uuid, ['sla_breached_at' => $slaBreachedAt]);
        }

        StageHistories::create([
            'flow_item_id'         => $item->id,
            'flow_pipeline_id'     => $item->flow_pipeline_id,
            'from_stage_id'        => null,
            'to_stage_id'          => $item->flow_stage_id,
            'moved_by_iam_user_id' => $item->iam_user_id,
            'moved_at'             => now(),
        ]);

        self::fireAutomations($item, null, $item->flow_stage_id, 'item_created');

        return $item;
    }

    /**
     * Updates the item. When flow_stage_id changes, validates required columns,
     * resets the checklist, records history, fires automations and notifies watchers.
     */
    public static function update($id, array $data)
    {
        $item = Items::where('uuid', $id)->first();

        if (!$item) {
            throw new NotAllowedException(
                'We cannot find the related object to update. ' .
                'Maybe you dont have the permission to update this object?'
            );
        }

        $isStageMove = false;
        $oldStageId  = $item->flow_stage_id;
        $newStageId  = null;

        if (array_key_exists('flow_stage_id', $data)) {
            $stage = Stages::withoutGlobalScopes()
                ->where('uuid', $data['flow_stage_id'])
                ->first();

            if (!$stage) {
                throw new NotAllowedException('Stage not found: ' . $data['flow_stage_id']);
            }

            $newStageId = $stage->id;
            // Replace UUID with the resolved integer so parent::update() skips re-conversion
            $data['flow_stage_id'] = $newStageId;

            if ($newStageId !== $oldStageId) {
                self::validateStageEntry($item, $newStageId);

                $isStageMove                   = true;
                $data['checklist_state']       = null;
                $data['last_stage_changed_at'] = now();
                // New stage visit: new SLA deadline, and no SLA action taken yet.
                $data['sla_breached_at']       = self::calculateSlaBreachedAt($stage, $data['last_stage_changed_at']);
                $data['sla_actioned_at']       = null;
            } else {
                Log::info('[ItemsService::update] flow_stage_id unchanged — no stage move, automations will not fire.', [
                    'flow_item_id' => $item->uuid,
                    'stage_id'     => $newStageId,
                ]);
            }
        }

        $model = parent::update($id, $data);

        Log::info('[ItemsService::update] Stage move check', [
            'flow_item_id'  => $item->uuid,
            'old_stage_id'  => $oldStageId,
            'new_stage_id'  => $newStageId,
            'is_stage_move' => $isStageMove,
        ]);

        if ($isStageMove) {
            StageHistories::create([
                'flow_item_id'         => $model->id,
                'flow_pipeline_id'     => $model->flow_pipeline_id,
                'from_stage_id'        => $oldStageId,
                'to_stage_id'          => $newStageId,
                'moved_by_iam_user_id' => UserHelper::me()->id,
                'moved_at'             => now(),
            ]);

            self::fireAutomations($model, $oldStageId, $newStageId, 'stage_exited');
            self::fireAutomations($model, $oldStageId, $newStageId, 'stage_entered');
            self::fireAutomations($model, $oldStageId, $newStageId, 'item_moved');
            self::notifyWatchers($model);
        }

        return $model;
    }

    /**
     * When the item's SLA in this stage runs out: entered + sla_days. It is stored
     * at stage entry (and may be in the future), so "breached" is simply
     * sla_breached_at <= now() — no scheduler needs to flip anything. NULL when the
     * stage has no SLA or is a won/lost stage (same rules as the old view logic).
     */
    public static function calculateSlaBreachedAt(?Stages $stage, $enteredAt): ?Carbon
    {
        if (!$stage || $stage->sla_days === null || $stage->is_won || $stage->is_lost || !$enteredAt) {
            return null;
        }

        return Carbon::parse($enteredAt)->addDays($stage->sla_days);
    }

    /**
     * Recalculates sla_breached_at for every item in a stage. Needed when the
     * stage's sla_days or won/lost flags change (see StagesService::update) and by
     * the backfill. Returns the number of items updated.
     */
    public static function recalculateSlaBreachedAtForStage(Stages $stage): int
    {
        $query = Items::withoutGlobalScopes()
            ->where('flow_stage_id', $stage->id)
            ->whereNull('deleted_at');

        if ($stage->deleted_at || $stage->sla_days === null || $stage->is_won || $stage->is_lost) {
            return $query->update(['sla_breached_at' => null]);
        }

        // Same arithmetic as calculateSlaBreachedAt, done in bulk in the database.
        return $query->whereNotNull('last_stage_changed_at')->update([
            'sla_breached_at' => DB::raw('last_stage_changed_at + make_interval(days => ' . (int) $stage->sla_days . ')'),
        ]);
    }

    /**
     * One-off backfill for items that existed before sla_breached_at /
     * sla_actioned_at were introduced (flow:backfill-sla-timestamps):
     *   - sla_breached_at for every item, stage by stage
     *   - sla_actioned_at from the newest SLA pusher log created during the item's
     *     current stage visit, so items that were already notified are not
     *     notified again right after the deploy
     */
    public static function backfillSlaTimestamps(): array
    {
        $stages = Stages::withoutGlobalScopes()->get();
        $breachedAtUpdated = 0;

        foreach ($stages as $stage) {
            $breachedAtUpdated += self::recalculateSlaBreachedAtForStage($stage);
        }

        // Every pusher ever used by an SLA automation, including disabled ones.
        $pusherIds = Automations::withoutGlobalScopes()
            ->where('trigger', 'sla_breached')
            ->whereNotNull('common_pusher_id')
            ->pluck('common_pusher_id')
            ->unique()
            ->values()
            ->all();

        // Newest SLA push per item, across its SLA pushers.
        $lastPushAt = [];

        foreach (self::getLastPushes($pusherIds) as $key => $push) {
            $uuid = substr($key, strpos($key, ':') + 1);

            if (!isset($lastPushAt[$uuid]) || $push['last_at']->gt($lastPushAt[$uuid])) {
                $lastPushAt[$uuid] = $push['last_at'];
            }
        }

        $actionedAtUpdated = 0;

        foreach (array_chunk(array_keys($lastPushAt), 500) as $uuids) {
            $items = Items::withoutGlobalScopes()
                ->whereIn('uuid', $uuids)
                ->whereNull('deleted_at')
                ->whereNull('sla_actioned_at')
                ->get(['id', 'uuid', 'last_stage_changed_at']);

            foreach ($items as $item) {
                $pushedAt = $lastPushAt[$item->uuid];

                // Only a push from the current stage visit counts.
                if ($item->last_stage_changed_at && $pushedAt->lt($item->last_stage_changed_at)) {
                    continue;
                }

                Items::withoutGlobalScopes()->where('id', $item->id)->update(['sla_actioned_at' => $pushedAt]);
                $actionedAtUpdated++;
            }
        }

        return [
            'sla_breached_at' => $breachedAtUpdated,
            'sla_actioned_at' => $actionedAtUpdated,
        ];
    }

    public static function delete($id)
    {
        $item = Items::withoutGlobalScopes()->where('uuid', $id)->first();

        if ($item) {
            self::fireAutomations($item, $item->flow_stage_id, null, 'item_deleted');
        }

        return parent::delete($id);
    }

    public static function restore(Items $item): Items
    {
        $item->restore();
        $item = $item->fresh();

        StageHistories::create([
            'flow_item_id'         => $item->id,
            'flow_pipeline_id'     => $item->flow_pipeline_id,
            'from_stage_id'        => null,
            'to_stage_id'          => $item->flow_stage_id,
            'moved_by_iam_user_id' => $item->iam_user_id,
            'moved_at'             => now(),
        ]);

        self::fireAutomations($item, null, $item->flow_stage_id, 'item_created');

        return $item;
    }

    /**
     * Marks a single checklist key as complete for the given item.
     */
    public static function completeChecklistItem(string $itemUuid, string $key): Items
    {
        $item  = Items::where('uuid', $itemUuid)->firstOrFail();
        $state = $item->checklist_state ?? [];

        $state[$key] = [
            'completed'    => true,
            'completed_by' => UserHelper::me()->id,
            'completed_at' => now()->toIso8601String(),
        ];

        $item->update(['checklist_state' => $state]);

        return $item->fresh();
    }

    /**
     * Marks a single checklist key as incomplete for the given item.
     */
    public static function uncompleteChecklistItem(string $itemUuid, string $key): Items
    {
        $item  = Items::where('uuid', $itemUuid)->firstOrFail();
        $state = $item->checklist_state ?? [];

        $state[$key] = [
            'completed'    => false,
            'completed_by' => null,
            'completed_at' => null,
        ];

        $item->update(['checklist_state' => $state]);

        return $item->fresh();
    }

    /**
     * Validates that all required columns for the target stage have values on this item.
     *
     * @throws NotAllowedException if any required column value is missing.
     */
    private static function validateStageEntry(Items $item, int $newStageId): void
    {
        $requiredColumnIds = StageRequiredColumns::withoutGlobalScopes()
            ->where('flow_stage_id', $newStageId)
            ->pluck('flow_column_id');

        if ($requiredColumnIds->isEmpty()) {
            return;
        }

        $filledColumnIds = ItemValues::withoutGlobalScopes()
            ->where('flow_item_id', $item->id)
            ->whereIn('flow_column_id', $requiredColumnIds)
            ->whereNotNull('value')
            ->pluck('flow_column_id');

        $missing = $requiredColumnIds->diff($filledColumnIds);

        if ($missing->isNotEmpty()) {
            throw new NotAllowedException(
                'Cannot move to this stage. Missing required column values for column IDs: ' .
                $missing->implode(', ')
            );
        }
    }

    /**
     * Queries active automations for the given trigger and fires each one via the Events system.
     */
    private static function fireAutomations(Items $item, ?int $fromStageId, ?int $toStageId, string $trigger): void
    {
        $query = Automations::withoutGlobalScope(\NextDeveloper\IAM\Database\Scopes\AuthorizationScope::class)
            ->where('flow_pipeline_id', $item->flow_pipeline_id)
            ->where('is_active', true)
            ->where('trigger', $trigger);

        if ($trigger === 'stage_entered') {
            $query->where(function ($q) use ($toStageId) {
                $q->whereNull('flow_stage_id')
                  ->orWhere('flow_stage_id', $toStageId);
            });
        } elseif ($trigger === 'stage_exited' || $trigger === 'stage_left') {
            $query->where(function ($q) use ($fromStageId) {
                $q->whereNull('flow_stage_id')
                  ->orWhere('flow_stage_id', $fromStageId);
            });
        }
        // item_moved, item_created, item_deleted have no stage filter

        $automations = $query->get();

        Log::info('[ItemsService::fireAutomations] Matched automations', [
            'flow_item_id'    => $item->uuid,
            'trigger'         => $trigger,
            'from_stage_id'   => $fromStageId,
            'to_stage_id'     => $toStageId,
            'automation_ids'  => $automations->pluck('id')->all(),
        ]);

        foreach ($automations as $automation) {
            if ($automation->common_pusher_id) {
                Log::info('[ItemsService::fireAutomations] Dispatching pusher for automation', [
                    'flow_item_id'    => $item->uuid,
                    'automation_id'   => $automation->id,
                    'common_pusher_id' => $automation->common_pusher_id,
                ]);

                self::triggerPusher($automation, $item);
            }

            if (!$automation->event_name) {
                continue;
            }

            Events::fire($automation->event_name, $item);
        }
    }

    /**
     * Fires a stage-change event so registered listeners can notify each watcher.
     */
    private static function notifyWatchers(Items $item): void
    {
        $hasWatchers = ItemWatchers::withoutGlobalScopes()->where('flow_item_id', $item->id)->exists();

        if (!$hasWatchers) {
            return;
        }

        Events::fire('item_stage_changed:NextDeveloper\Flow\Items', $item);
    }

    /**
     * Used by the hourly SLA breach check. The check re-fires every hour while the
     * item is still in the breached stage, so when the pushers queue lags, each run
     * used to queue another pusher log for the same item — and once the queue caught
     * up, every one of them sent the email again. We now skip the trigger while a
     * previous log for the same pusher + item is still waiting to be executed.
     */
    public static function triggerPusherForAutomation(Automations $automation, Items $item): void
    {
        if (self::hasPendingPush($automation->common_pusher_id, $item)) {
            Log::info('[Flow] Skipping pusher trigger — a pending push already exists for this item.', [
                'flow_automation_id' => $automation->id,
                'common_pusher_id'   => $automation->common_pusher_id,
                'flow_item_id'       => $item->uuid,
            ]);

            return;
        }

        self::triggerPusher($automation, $item);
    }

    /**
     * Returns the ids of items whose SLA is breached, keyed by id for fast lookup.
     *
     * Reads flow_items_perspective.sla_breached — the same value the UI shows —
     * instead of recomputing it in PHP. The previous PHP rule floored the day count
     * ((int) diffInDays), so the check fired up to a full day after the UI already
     * showed the item as breached. Using the view keeps backend and UI identical.
     */
    public static function getSlaBreachedItemIds(): array
    {
        return ItemsPerspective::withoutGlobalScopes()
            ->where('sla_breached', true)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->flip()
            ->all();
    }

    /**
     * Finds SLA-breached items and dispatches one TriggerSlaBreachAutomationJob per
     * matching (item, automation) pair. Used by flow:check-sla-breaches and
     * CheckSlaBreachesJob.
     *
     * Kept cheap on purpose — it runs inside the hourly scheduler, and the old inline
     * version (per-item automation query, per-item JSON scan of common_pusher_logs,
     * transformers and log writes) ran long enough to hold up the whole schedule:
     *   - automations are loaded first; ones whose only action is a missing or
     *     disabled pusher are dropped, and only breached items (sla_breached_at
     *     passed) in a pipeline/stage covered by a remaining automation are loaded
     *   - automations are matched to items in memory
     *   - the heavy work (transform + pusher log + event) runs in the queued job
     *
     * Automations fire once per stage visit (sla_actioned_at), and again only after
     * the pusher is changed by its owner (see slaAlreadyActioned), instead of every
     * hour while the item stays breached.
     *
     * Returns a report per breached item for the command's output:
     *   [['item' => Items, 'stage' => Stages, 'automations' => [['automation' => Automations, 'status' => string]]]]
     * Status is one of: dispatched, dry_run, skipped_already_fired.
     */
    public static function checkSlaBreaches(bool $dryRun = false): array
    {
        // Start from the automations, not the items: only items an automation could
        // actually act on are worth loading. Most breached items have no SLA
        // automation at all, and some point at a disabled pusher.
        $automations = self::getActionableSlaAutomations();

        if ($automations->isEmpty()) {
            return [];
        }

        // Pipeline-wide automations (no stage) cover every stage of their pipeline;
        // the rest cover only their own stage.
        $pipelineIds = $automations->whereNull('flow_stage_id')->pluck('flow_pipeline_id')->unique()->values()->all();
        $stageIds    = $automations->whereNotNull('flow_stage_id')->pluck('flow_stage_id')->unique()->values()->all();

        // Breached = the stored deadline has passed (set at stage entry, see
        // calculateSlaBreachedAt). Straight from flow_items — no view needed.
        $items = Items::withoutGlobalScopes()
            ->whereNull('deleted_at')
            ->whereNotNull('sla_breached_at')
            ->where('sla_breached_at', '<=', now())
            ->where(function ($query) use ($pipelineIds, $stageIds) {
                $query->whereIn('flow_pipeline_id', $pipelineIds ?: [0])
                      ->orWhereIn('flow_stage_id', $stageIds ?: [0]);
            })
            ->get();

        if ($items->isEmpty()) {
            return [];
        }

        // The view does not check stages.deleted_at; the old check skipped deleted stages.
        $stages = Stages::withoutGlobalScopes()
            ->whereIn('id', $items->pluck('flow_stage_id')->unique()->values())
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('id');

        $pusherIds = $automations->pluck('common_pusher_id')->filter()->unique()->values()->all();

        // When each pusher was last changed. Editing it (e.g. fixing its URL) or
        // re-enabling it bumps updated_at, which gives its items a fresh try.
        $pusherUpdatedAt = $pusherIds
            ? Pushers::withoutGlobalScopes()->whereIn('id', $pusherIds)->get(['id', 'updated_at'])->keyBy('id')
            : collect();

        $report      = [];
        $actionedIds = [];

        foreach ($items as $item) {
            $stage = $stages->get($item->flow_stage_id);

            if (!$stage) {
                continue;
            }

            // Same matching as before: pipeline-wide (no stage) or this item's stage.
            $matching = $automations->filter(function ($automation) use ($item) {
                return $automation->flow_pipeline_id === $item->flow_pipeline_id
                    && ($automation->flow_stage_id === null || $automation->flow_stage_id === $item->flow_stage_id);
            });

            if ($matching->isEmpty()) {
                continue;
            }

            $entry = ['item' => $item, 'stage' => $stage, 'automations' => []];

            foreach ($matching as $automation) {
                $alreadyActioned = self::slaAlreadyActioned(
                    $item,
                    $automation->common_pusher_id ? $pusherUpdatedAt->get($automation->common_pusher_id)?->updated_at : null
                );

                if ($alreadyActioned) {
                    $status = 'skipped_already_fired';
                } elseif ($dryRun) {
                    $status = 'dry_run';
                } else {
                    TriggerSlaBreachAutomationJob::dispatch($item->id, $automation->id, $item->flow_stage_id);
                    $status = 'dispatched';
                    $actionedIds[$item->id] = true;
                }

                $entry['automations'][] = ['automation' => $automation, 'status' => $status];
            }

            $report[] = $entry;
        }

        // Mark as actioned at dispatch time (not when the job runs), so the next
        // hourly run does not queue the same item again while its job still waits.
        foreach (array_chunk(array_keys($actionedIds), 1000) as $ids) {
            Items::withoutGlobalScopes()->whereIn('id', $ids)->update(['sla_actioned_at' => now()]);
        }

        return $report;
    }

    /**
     * SLA automations fire once per stage visit: once sla_actioned_at is set (it is
     * cleared on every stage move) they are not repeated every hour, whether the
     * push succeeded or failed. The one exception: if the automation's pusher was
     * changed by its owner after that (fixed URL, re-enabled — bumps updated_at),
     * the item gets another try, since the previous attempt may have failed only
     * because of the broken pusher.
     */
    private static function slaAlreadyActioned(Items $item, $pusherUpdatedAt): bool
    {
        if (!$item->sla_actioned_at) {
            return false;
        }

        if ($pusherUpdatedAt && $pusherUpdatedAt->gt($item->sla_actioned_at)) {
            return false;
        }

        return true;
    }

    /**
     * Active sla_breached automations that can actually do something: they fire an
     * event, or they push through a pusher that exists, has a URL and is not
     * disabled. An automation whose only action is a disabled pusher is skipped —
     * PushersService::trigger() would drop the push anyway.
     */
    private static function getActionableSlaAutomations(): Collection
    {
        $automations = Automations::withoutGlobalScopes()
            ->where('trigger', 'sla_breached')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->get();

        $pusherIds = $automations->pluck('common_pusher_id')->filter()->unique()->values()->all();

        $usablePusherIds = $pusherIds
            ? Pushers::withoutGlobalScopes()
                ->whereIn('id', $pusherIds)
                ->whereNull('deleted_at')
                ->whereNotNull('url')
                ->where('status', '!=', 'disabled')
                ->pluck('id')
                ->flip()
                ->all()
            : [];

        return $automations->filter(function ($automation) use ($usablePusherIds) {
            return $automation->event_name
                || ($automation->common_pusher_id && isset($usablePusherIds[$automation->common_pusher_id]));
        })->values();
    }

    /**
     * Fires one sla_breached automation for one item. Called by
     * TriggerSlaBreachAutomationJob; reloads everything so a job that waited in the
     * queue does not act on stale data.
     */
    public static function fireSlaBreachAutomation(int $itemId, int $automationId, int $stageId, bool $firePusher = true): void
    {
        $item = Items::withoutGlobalScopes()
            ->where('id', $itemId)
            ->whereNull('deleted_at')
            ->first();

        // The item may have been deleted or moved while the job was queued.
        if (!$item || $item->flow_stage_id !== $stageId) {
            Log::info('[Flow] Skipping SLA automation — item deleted or left the breached stage.', [
                'flow_item_id'       => $itemId,
                'flow_automation_id' => $automationId,
            ]);

            return;
        }

        $automation = Automations::withoutGlobalScopes()
            ->where('id', $automationId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();

        if (!$automation) {
            return;
        }

        // $firePusher is false when the push already ran for this stage visit and only
        // the automation's event should fire.
        if ($firePusher && $automation->common_pusher_id) {
            // Still checks for a pending log: two hourly runs can dispatch jobs for
            // the same item before either one has created its log.
            self::triggerPusherForAutomation($automation, $item);
        }

        if ($automation->event_name) {
            Events::fire($automation->event_name, $item);
        }
    }

    /**
     * For every (pusher, item) pair of the given pushers: when the latest pusher log
     * was created and whether one is still pending. One grouped query instead of one
     * JSON scan per item. The item's uuid is stored as "id" in the pushed payload.
     *
     * @return array<string, array{last_at: \Carbon\Carbon, has_pending: bool}> keyed "pusherId:itemUuid"
     */
    private static function getLastPushes(array $commonPusherIds): array
    {
        if (!$commonPusherIds) {
            return [];
        }

        return PusherLogs::withoutGlobalScopes()
            ->whereIn('common_pusher_id', $commonPusherIds)
            ->whereNull('deleted_at')
            ->selectRaw("common_pusher_id, body->>'id' as flow_item_uuid, max(created_at) as last_at, bool_or(status = 'pending') as has_pending")
            ->groupByRaw("common_pusher_id, body->>'id'")
            ->toBase()
            ->get()
            ->mapWithKeys(fn ($row) => [
                $row->common_pusher_id . ':' . $row->flow_item_uuid => [
                    'last_at'     => \Carbon\Carbon::parse($row->last_at),
                    'has_pending' => (bool) $row->has_pending,
                ],
            ])
            ->all();
    }

    /**
     * Returns true when a pusher log for the given pusher and item has not been
     * executed yet. The item's uuid is stored as "id" in the pushed payload
     * (see triggerPusher / transformObject).
     */
    private static function hasPendingPush(int $commonPusherId, Items $item): bool
    {
        return PusherLogs::withoutGlobalScopes()
            ->where('common_pusher_id', $commonPusherId)
            ->where('status', 'pending')
            ->whereNull('deleted_at')
            ->where('body->id', $item->uuid)
            ->exists();
    }

    /**
     * Returns true when the item is no longer in the stage captured in a pusher
     * payload (or no longer exists). Pusher logs are executed asynchronously and can
     * run long after they were created; pushers call this before acting so a stale
     * log does not repeat an action (e.g. an email) for an item that already moved.
     *
     * When the payload carries no stage, there is nothing to compare, so the log is
     * treated as current.
     */
    public static function hasLeftStage(string $itemUuid, ?string $snapshotStageUuid): bool
    {
        if (empty($snapshotStageUuid)) {
            return false;
        }

        $item = Items::withoutGlobalScopes()
            ->where('uuid', $itemUuid)
            ->whereNull('deleted_at')
            ->first();

        if (!$item) {
            return true;
        }

        $currentStageUuid = Stages::withoutGlobalScopes()
            ->where('id', $item->flow_stage_id)
            ->value('uuid');

        return $currentStageUuid !== $snapshotStageUuid;
    }

    private static function triggerPusher(Automations $automation, Items $item): void
    {
        $object = self::resolveObject($item->object_type, $item->object_id);

        $payload = array_merge(
            $automation->payload_template ?? [],
            self::transformObject($item),
            ['object' => $object ? self::transformObject($object) : null]
        );

        try {
            PushersService::trigger($automation->common_pusher_id, $payload);
        } catch (\Throwable $e) {
            Log::warning('[Flow] Pusher trigger failed for automation ' . $automation->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Transforms a model through its corresponding HTTP transformer if one exists.
     * Falls back to toArray() when no transformer class can be found.
     */
    private static function transformObject(object $object): array
    {
        $modelClass       = get_class($object);
        $transformerClass = str_replace('\\Database\\Models\\', '\\Http\\Transformers\\', $modelClass) . 'Transformer';

        if (class_exists($transformerClass)) {
            try {
                return (new $transformerClass())->transform($object);
            } catch (\Throwable $e) {
                Log::warning('[Flow] Transformer failed for ' . $modelClass . ': ' . $e->getMessage());
            }
        }

        return $object->toArray();
    }

    private static function resolveObject(string $objectType, int $id): ?object
    {
        $parts      = array_values(array_filter(explode('\\', $objectType)));
        $className  = array_pop($parts);
        $modelClass = '\\' . implode('\\', $parts) . '\\Database\\Models\\' . $className;

        if (!class_exists($modelClass)) {
            return null;
        }

        return $modelClass::withoutGlobalScopes()->where('id', $id)->first();
    }

    /**
     * Converts a short namespace (e.g. \NextDeveloper\CRM\Opportunities) and a UUID
     * into the integer primary key by routing through the full model class so that
     * authorization scopes are enforced and arbitrary table access is prevented.
     */
    private static function resolveObjectId(string $objectType, string $uuid): int
    {
        $parts      = array_values(array_filter(explode('\\', $objectType)));
        $className  = array_pop($parts);
        $modelClass = '\\' . implode('\\', $parts) . '\\Database\\Models\\' . $className;

        if (!class_exists($modelClass)) {
            throw new NotAllowedException('Invalid object_type: ' . $objectType);
        }

        $object = $modelClass::where('uuid', $uuid)->first();

        if (!$object) {
            throw new NotAllowedException(
                'Cannot find or access the referenced object. ' .
                'Check that the object exists and that you have permission to use it.'
            );
        }

        return $object->id;
    }
}
