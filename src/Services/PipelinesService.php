<?php

namespace NextDeveloper\Flow\Services;

use Illuminate\Support\Facades\DB;
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
    public static function createFromTemplate(string $templateId, ?string $name = null): Pipelines
    {
        $template = config("flow.pipeline_templates.{$templateId}");

        if (!$template) {
            throw new NotAllowedException('Pipeline template not found.');
        }

        $accountId = UserHelper::currentAccount()->id;
        $userId    = UserHelper::me()->id;

        return DB::transaction(function () use ($template, $name, $accountId, $userId) {
            $pipeline = Pipelines::create([
                'name'           => $name ?? $template['name'],
                'description'    => $template['description'],
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
}
