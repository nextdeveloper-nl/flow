<?php

namespace NextDeveloper\Flow\Services;

use Carbon\Carbon;
use Illuminate\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\CRM\Database\Models\AccountsPerspective;
use NextDeveloper\CRM\Database\Models\OpportunitiesPerspective;
use NextDeveloper\Flow\Database\Models\ItemsPerspective;
use NextDeveloper\Flow\Database\Models\StagesPerspective;
use NextDeveloper\Flow\Http\Transformers\PipelinesTransformer;
use NextDeveloper\Flow\Services\AbstractServices\AbstractPipelinesService;
use NextDeveloper\Flow\Database\Models\Pipelines;
use NextDeveloper\Flow\Database\Models\Stages;
use NextDeveloper\Flow\Database\Models\Columns;
use NextDeveloper\Flow\Database\Models\StageRequiredColumns;
use NextDeveloper\Flow\Database\Models\Automations;
use NextDeveloper\IAM\Helpers\UserHelper;
use NextDeveloper\Commons\Exceptions\NotAllowedException;

/**
 * This class is responsible from managing the data for Pipelines
 *
 * Class PipelinesService.
 *
 * @package NextDeveloper\Flow\Database\Models
 */
class PipelinesService extends AbstractPipelinesService
{
    /**
     * Clones a template pipeline into a live pipeline for the current account.
     * Copies stages, columns, stage_required_columns and automations — remapping all IDs.
     *
     * @throws NotAllowedException if the referenced pipeline is not a template.
     */
    public static function cloneFromTemplate(string $templateUuid, ?string $name = null): Pipelines
    {
        $template = Pipelines::withoutGlobalScopes()
            ->where('uuid', $templateUuid)
            ->where('is_template', true)
            ->first();

        if (!$template) {
            throw new NotAllowedException('Pipeline not found or is not a template.');
        }

        $accountId = UserHelper::currentAccount()->id;
        $userId    = UserHelper::me()->id;

        return DB::transaction(function () use ($template, $name, $accountId, $userId) {
            // 1. Create new pipeline from template
            $pipeline = Pipelines::create([
                'name'           => $name ?? $template->name,
                'description'    => $template->description,
                'object_type'    => $template->object_type,
                'is_template'    => false,
                'is_system'      => false,
                'is_active'      => $template->is_active,
                'iam_account_id' => $accountId,
                'iam_user_id'    => $userId,
            ]);

            // 2. Clone stages — build old → new ID map
            $stageIdMap = [];
            $templateStages = Stages::withoutGlobalScopes()
                ->where('flow_pipeline_id', $template->id)
                ->whereNull('deleted_at')
                ->get();

            foreach ($templateStages as $stage) {
                $newStage = Stages::create([
                    'flow_pipeline_id' => $pipeline->id,
                    'name'             => $stage->name,
                    'color'            => $stage->color,
                    'position'         => $stage->position,
                    'probability'      => $stage->probability,
                    'sla_days'         => $stage->sla_days,
                    'is_won'           => $stage->is_won,
                    'is_lost'          => $stage->is_lost,
                    'checklist'        => $stage->checklist,
                    'is_active'        => $stage->is_active,
                ]);
                $stageIdMap[$stage->id] = $newStage->id;
            }

            // 3. Clone columns — build old → new ID map
            $columnIdMap = [];
            $templateColumns = Columns::withoutGlobalScopes()
                ->where('flow_pipeline_id', $template->id)
                ->whereNull('deleted_at')
                ->get();

            foreach ($templateColumns as $column) {
                $newColumn = Columns::create([
                    'flow_pipeline_id' => $pipeline->id,
                    'name'             => $column->name,
                    'label'            => $column->label,
                    'field_type'       => $column->field_type,
                    'options'          => $column->options,
                    'default_value'    => $column->default_value,
                    'is_required'      => $column->is_required,
                    'position'         => $column->position,
                    'is_active'        => $column->is_active,
                ]);
                $columnIdMap[$column->id] = $newColumn->id;
            }

            // 4. Clone stage_required_columns using the remapped stage and column IDs
            $requiredColumns = StageRequiredColumns::whereIn('flow_stage_id', array_keys($stageIdMap))->get();

            foreach ($requiredColumns as $req) {
                if (!isset($stageIdMap[$req->flow_stage_id], $columnIdMap[$req->flow_column_id])) {
                    continue;
                }
                StageRequiredColumns::create([
                    'flow_stage_id'  => $stageIdMap[$req->flow_stage_id],
                    'flow_column_id' => $columnIdMap[$req->flow_column_id],
                ]);
            }

            // 5. Clone automations using the remapped stage IDs
            $templateAutomations = Automations::withoutGlobalScopes()
                ->where('flow_pipeline_id', $template->id)
                ->whereNull('deleted_at')
                ->get();

            foreach ($templateAutomations as $automation) {
                $newStageId = $automation->flow_stage_id
                    ? ($stageIdMap[$automation->flow_stage_id] ?? null)
                    : null;

                Automations::create([
                    'flow_pipeline_id' => $pipeline->id,
                    'flow_stage_id'    => $newStageId,
                    'trigger'          => $automation->trigger,
                    'event_name'       => $automation->event_name,
                    'payload_template' => $automation->payload_template,
                    'is_active'        => $automation->is_active,
                    'iam_account_id'   => $accountId,
                ]);
            }

            return $pipeline->fresh();
        });
    }

    /**
     * Returns the catalog of pipeline templates defined in config('flow.pipeline_templates'),
     * for the "start with a template" picker UI.
     *
     * @return array
     */
    public static function listTemplates(): array
    {
        $templates = config('flow.pipeline_templates', []);

        $result = [];
        foreach ($templates as $id => $template) {
            $result[] = [
                'id'            => $id,
                'name'          => $template['name'],
                'description'   => $template['description'],
                'icon'          => $template['icon'],
                'campaign_type' => $template['campaign_type'],
                'kind'          => $template['kind'] ?? self::KIND_CUSTOM,
                'stages'        => $template['stages'],
            ];
        }

        return $result;
    }

    /**
     * Creates a real pipeline + ordered stages for the current account from a config-defined
     * template (config('flow.pipeline_templates')). Unlike cloneFromTemplate(), this does not
     * read from a DB "is_template" row — the template definition itself lives in config.
     *
     * @throws NotAllowedException if the template id does not exist in config.
     */
    public static function createFromTemplate(?string $templateId, ?string $name = null, ?string $kind = null): Pipelines
    {
        if ($kind !== null && !in_array($kind, self::KINDS, true)) {
            throw new NotAllowedException('Unknown pipeline kind: ' . $kind);
        }

        // No template: start blank for the kind (leo4#1241).
        $templateId ??= 'blank-' . ($kind ?? self::KIND_CUSTOM);

        $template = config("flow.pipeline_templates.{$templateId}");

        if (!$template) {
            throw new NotAllowedException('Pipeline template not found.');
        }

        $kind ??= $template['kind'] ?? self::KIND_CUSTOM;

        $accountId = UserHelper::currentAccount()->id;
        $userId    = UserHelper::me()->id;

        return DB::transaction(function () use ($template, $name, $kind, $accountId, $userId) {
            $pipeline = Pipelines::create([
                'name'           => $name ?? $template['name'],
                'description'    => $template['description'],
                'kind'           => $kind,
                // What the items are. A campaign pipeline is re-pointed at its campaign afterwards.
                'object_type'    => self::itemObjectTypeForKind($kind),
                'is_template'    => false,
                'is_system'      => false,
                'is_active'      => true,
                'iam_account_id' => $accountId,
                'iam_user_id'    => $userId,
            ]);

            foreach ($template['stages'] as $position => $stage) {
                Stages::create([
                    'flow_pipeline_id' => $pipeline->id,
                    'name'             => $stage['name'],
                    'color'            => $stage['color'],
                    'position'         => $position,
                    'probability'      => $stage['probability'],
                    'sla_days'         => $stage['sla_days'],
                    'is_won'           => $stage['is_won'],
                    'is_lost'          => $stage['is_lost'],
                    'checklist'        => null,
                    'is_active'        => true,
                ]);
            }

            return $pipeline->fresh();
        });
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public const KIND_SALES = 'sales';
    public const KIND_MARKETING = 'marketing';
    public const KIND_PROJECT = 'project';
    public const KIND_CUSTOM = 'custom';

    /** What a pipeline is for (flow_pipelines.kind). */
    public const KINDS = [self::KIND_SALES, self::KIND_MARKETING, self::KIND_PROJECT, self::KIND_CUSTOM];

    /**
     * The item type a new pipeline of this kind is designed for. Sales, marketing and custom
     * pipelines hold opportunities for now; project pipelines hold tasks (leo4#1242).
     */
    public static function itemObjectTypeForKind(string $kind): string
    {
        return $kind === self::KIND_PROJECT ? 'NextDeveloper\CRM\Tasks' : self::BOARD_OPPORTUNITY_TYPE;
    }

    /**
     * Item object_type of CRM opportunities as stored in flow_items.object_type (short form).
     */
    private const BOARD_OPPORTUNITY_TYPE = 'NextDeveloper\CRM\Opportunities';

    /**
     * Safety-net TTL (seconds) of the board snapshot. Write paths invalidate explicitly
     * (see invalidateBoard); the TTL only heals changes made outside Flow, such as an
     * account owner or opportunity edit, and bounds SLA time drift.
     */
    private const BOARD_CACHE_TTL = 300;

    /**
     * Returns everything the pipeline board page needs in one payload: pipeline, stages with
     * stats, ALL items of every stage with opportunity owner data, and the linked campaign.
     *
     * The payload is a disposable Redis snapshot (per pipeline + per current account, so
     * AuthorizationScope results are never shared across accounts). Pass $refresh = true to
     * drop and rebuild it from scratch; it is always safe to delete the keys.
     *
     * @param string $pipelineUuid
     * @param bool   $refresh      forget the snapshot and rebuild it
     * @return array
     */
    public static function board(string $pipelineUuid, bool $refresh = false): array
    {
        // Authorization: the pipeline is fetched through the scoped model on every request,
        // so a user can never read a snapshot of a pipeline they cannot see.
        $pipeline = Pipelines::where('uuid', $pipelineUuid)->firstOrFail();

        $key = self::boardCacheKey($pipeline->uuid, UserHelper::currentAccount()->id);

        if ($refresh) {
            Cache::forget($key);
        }

        $cached = Cache::get($key);

        if ($cached !== null) {
            return $cached;
        }

        // Only one request builds a missing snapshot; the others wait and then read it.
        try {
            return Cache::lock('lock:' . $key, 30)->block(10, function () use ($key, $pipeline) {
                $cached = Cache::get($key);

                if ($cached !== null) {
                    return $cached;
                }

                $payload = self::buildBoard($pipeline);
                Cache::put($key, $payload, self::BOARD_CACHE_TTL);

                return $payload;
            });
        } catch (LockTimeoutException $e) {
            // The builder is taking too long; build our own copy instead of failing.
            return self::buildBoard($pipeline);
        }
    }

    /**
     * Drops every cached board snapshot of a pipeline (all accounts) by bumping the pipeline's
     * snapshot version; the old entries are no longer addressable and expire by TTL.
     * Called from the services that write items, stages and pipelines.
     *
     * @param string|null $pipelineUuid UUID of the pipeline (not the integer id)
     */
    public static function invalidateBoard(?string $pipelineUuid): void
    {
        if (!$pipelineUuid) {
            return;
        }

        $versionKey = CacheHelper::getKey('FlowBoard', $pipelineUuid, 'version');

        // add() only sets the key when missing, so increment() always works on an integer.
        Cache::add($versionKey, 1, 86400 * 30);
        Cache::increment($versionKey);
    }

    /**
     * Same as invalidateBoard() but takes the integer pipeline id (what items and stages store).
     */
    public static function invalidateBoardByPipelineId(?int $pipelineId): void
    {
        if (!$pipelineId) {
            return;
        }

        self::invalidateBoard(Pipelines::withoutGlobalScopes()->where('id', $pipelineId)->value('uuid'));
    }

    /**
     * Drops the board snapshots of one pipeline, or of every pipeline when $pipelineUuid is null
     * (flow:rebuild-board). Snapshots are rebuilt from scratch lazily on the next board request.
     *
     * @return int number of pipelines invalidated
     */
    public static function invalidateAllBoards(?string $pipelineUuid = null): int
    {
        $query = Pipelines::withoutGlobalScopes();

        if ($pipelineUuid) {
            $query->where('uuid', $pipelineUuid);
        }

        $uuids = $query->pluck('uuid');

        foreach ($uuids as $uuid) {
            self::invalidateBoard($uuid);
        }

        return $uuids->count();
    }

    /**
     * Updates the pipeline and drops its board snapshots.
     */
    public static function update($id, array $data)
    {
        $model = parent::update($id, $data);

        self::invalidateBoard($model->uuid);

        return $model;
    }

    /**
     * Deletes the pipeline and drops its board snapshots.
     */
    public static function delete($id)
    {
        $result = parent::delete($id);

        self::invalidateBoard($id);

        return $result;
    }

    /**
     * Cache key of a board snapshot: pipeline uuid + invalidation version + account.
     */
    private static function boardCacheKey(string $pipelineUuid, int $accountId): string
    {
        $version = (int) Cache::get(CacheHelper::getKey('FlowBoard', $pipelineUuid, 'version'), 1);

        return CacheHelper::getKey('FlowBoard', $pipelineUuid, 'v' . $version . ':acc:' . $accountId);
    }

    /**
     * Builds the board payload with a fixed number of queries, regardless of item count:
     * stages, items, opportunities, accounts and campaign. Every query runs through the
     * models' AuthorizationScope; only LimitScope (row cap of 20) is removed.
     */
    private static function buildBoard(Pipelines $pipeline): array
    {
        // 1. Stages, in board order.
        $stages = StagesPerspective::withoutGlobalScope(LimitScope::class)
            ->where('flow_pipeline_id', $pipeline->id)
            ->orderBy('position')
            ->get();

        $stageUuids = $stages->pluck('uuid', 'id');

        // 2. ALL items of the pipeline in one query; grouped by stage below.
        $items = ItemsPerspective::withoutGlobalScope(LimitScope::class)
            ->where('flow_pipeline_id', $pipeline->id)
            ->orderBy('position')
            ->orderByDesc('created_at')
            ->get();

        // 3. Opportunities for every opportunity item in one query (gives uuid + account id).
        $opportunityIds = $items
            ->where('object_type', self::BOARD_OPPORTUNITY_TYPE)
            ->pluck('object_id')
            ->unique()
            ->values();

        $opportunities = $opportunityIds->isEmpty()
            ? collect()
            : OpportunitiesPerspective::withoutGlobalScope(LimitScope::class)
                ->whereIn('id', $opportunityIds)
                ->get(['id', 'uuid', 'crm_account_id'])
                ->keyBy('id');

        // 4. Account owner data for those opportunities in one query.
        $accountIds = $opportunities->pluck('crm_account_id')->filter()->unique()->values();

        $accounts = $accountIds->isEmpty()
            ? collect()
            : AccountsPerspective::withoutGlobalScope(LimitScope::class)
                ->whereIn('id', $accountIds)
                ->get(['id', 'account_owners_fullname', 'account_owners_email', 'domain_name'])
                ->keyBy('id');

        // 5. Object uuids the scoped lookup could not see (and non-opportunity items), resolved
        //    like ItemsPerspectiveTransformer does, but batched: one query per object type.
        $objectUuids = self::resolveObjectUuids($items, $opportunities);

        // 6. Shape the items per stage and compute the stage stats from them, so the stats
        //    always match the items shown (the view-based stats come from a cached transformer).
        $now      = now();
        $byStage  = [];

        foreach ($items as $item) {
            if (!isset($stageUuids[$item->flow_stage_id])) {
                continue;
            }

            $opportunity = $item->object_type === self::BOARD_OPPORTUNITY_TYPE
                ? $opportunities->get($item->object_id)
                : null;
            $account = $opportunity ? $accounts->get($opportunity->crm_account_id) : null;

            $byStage[$item->flow_stage_id][] = [
                'id'                    => $item->uuid,
                'flow_pipeline_id'      => $pipeline->uuid,
                'flow_stage_id'         => $stageUuids[$item->flow_stage_id],
                'object_type'           => $item->object_type,
                'object_id'             => $objectUuids[$item->object_type][$item->object_id] ?? null,
                'object_name'           => $item->object_name,
                'object_subtitle'       => $item->object_subtitle,
                'object_value'          => $item->object_value,
                'position'              => $item->position,
                'checklist_state'       => $item->checklist_state,
                'last_stage_changed_at' => $item->last_stage_changed_at?->toIso8601String(),
                'sla_breached'          => (bool) $item->sla_breached,
                'sla_breached_at'       => $item->sla_breached_at?->toIso8601String(),
                'sla_actioned_at'       => $item->sla_actioned_at?->toIso8601String(),
                'created_at'            => $item->created_at?->toIso8601String(),
                'updated_at'            => $item->updated_at?->toIso8601String(),
                'owner'                 => $account ? [
                    'fullname'    => $account->account_owners_fullname,
                    'email'       => $account->account_owners_email,
                    'domain_name' => $account->domain_name,
                ] : null,
            ];
        }

        $stagePayload = [];

        foreach ($stages as $stage) {
            $stageItems = $byStage[$stage->id] ?? [];
            $daysInStage = array_filter(array_map(
                fn ($i) => $i['last_stage_changed_at']
                    ? Carbon::parse($i['last_stage_changed_at'])->diffInSeconds($now) / 86400
                    : null,
                $stageItems
            ), fn ($d) => $d !== null);

            $stagePayload[] = [
                'id'                 => $stage->uuid,
                'flow_pipeline_id'   => $pipeline->uuid,
                'name'               => $stage->name,
                'color'              => $stage->color,
                'position'           => $stage->position,
                'probability'        => $stage->probability,
                'sla_days'           => $stage->sla_days,
                'is_won'             => (bool) $stage->is_won,
                'is_lost'            => (bool) $stage->is_lost,
                'is_active'          => (bool) $stage->is_active,
                'checklist'          => $stage->checklist,
                'item_count'         => count($stageItems),
                'sla_breached_count' => count(array_filter($stageItems, fn ($i) => $i['sla_breached'])),
                'avg_days_in_stage'  => $daysInStage ? round(array_sum($daysInStage) / count($daysInStage), 1) : null,
                'items'              => $stageItems,
            ];
        }

        // 7. Linked CRM campaign (one query); skipped when the CRM package is not installed.
        $campaign = null;
        $campaignModel = '\NextDeveloper\CRM\Database\Models\Campaigns';

        if (class_exists($campaignModel)) {
            $found = $campaignModel::withoutGlobalScope(LimitScope::class)
                ->where('flow_pipeline_id', $pipeline->id)
                ->first();

            $campaign = $found
                ? app('\NextDeveloper\CRM\Http\Transformers\CampaignsTransformer')->transform($found)
                : null;
        }

        return [
            'pipeline' => app(PipelinesTransformer::class)->transform($pipeline),
            'stages'   => $stagePayload,
            'campaign' => $campaign,
            'built_at' => $now->toIso8601String(),
        ];
    }

    /**
     * Maps [object_type][integer object_id] => uuid for every item. Opportunities already loaded
     * through the scoped query reuse that result; the rest are looked up unscoped, one query per
     * object type (the same unscoped uuid lookup ItemsPerspectiveTransformer does per item).
     */
    private static function resolveObjectUuids($items, $opportunities): array
    {
        $uuids = [];

        foreach ($opportunities as $id => $opportunity) {
            $uuids[self::BOARD_OPPORTUNITY_TYPE][$id] = $opportunity->uuid;
        }

        foreach ($items->groupBy('object_type') as $objectType => $typeItems) {
            $missing = $typeItems->pluck('object_id')->unique()
                ->reject(fn ($id) => isset($uuids[$objectType][$id]))
                ->values();

            if ($missing->isEmpty()) {
                continue;
            }

            $parts      = array_values(array_filter(explode('\\', $objectType)));
            $className  = array_pop($parts);
            $modelClass = '\\' . implode('\\', $parts) . '\\Database\\Models\\' . $className;

            if (!class_exists($modelClass)) {
                continue;
            }

            foreach ($modelClass::withoutGlobalScopes()->whereIn('id', $missing)->pluck('uuid', 'id') as $id => $uuid) {
                $uuids[$objectType][$id] = $uuid;
            }
        }

        return $uuids;
    }

    /**
     * Restores a soft-deleted pipeline.
     *
     * @throws NotAllowedException if the pipeline cannot be found.
     */
    public static function restore($id): Pipelines
    {
        $model = Pipelines::withTrashed()->where('uuid', $id)->first();

        if (!$model) {
            throw new NotAllowedException(
                'We cannot find the related object to restore. ' .
                'Maybe you dont have the permission to update this object?'
            );
        }

        $model->restore();

        return $model->fresh();
    }
}
