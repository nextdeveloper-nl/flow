<?php

namespace NextDeveloper\Flow\Authorization\Roles;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use NextDeveloper\IAM\Authorization\Roles\AbstractRole;
use NextDeveloper\IAM\Authorization\Roles\IAuthorizationRole;
use NextDeveloper\IAM\Database\Models\Users;

class FlowAdminRole extends AbstractRole implements IAuthorizationRole
{
    public const NAME = 'flow-admin';

    public const LEVEL = 100;

    public const DESCRIPTION = 'Flow administrator with full access to all flow objects across all accounts.';

    public const DB_PREFIX = 'flow';

    /**
     * Admin sees everything — no additional WHERE conditions applied.
     */
    public function apply(Builder $builder, Model $model)
    {
        //  No restrictions for admin
    }

    public function checkPrivileges(?Users $users = null)
    {
        //
    }

    public function getModule()
    {
        return 'flow';
    }

    public function allowedOperations(): array
    {
        return [
            'flow_pipelines:read',
            'flow_pipelines:create',
            'flow_pipelines:update',
            'flow_pipelines:delete',

            'flow_stages:read',
            'flow_stages:create',
            'flow_stages:update',
            'flow_stages:delete',

            'flow_columns:read',
            'flow_columns:create',
            'flow_columns:update',
            'flow_columns:delete',

            'flow_stage_required_columns:read',
            'flow_stage_required_columns:create',
            'flow_stage_required_columns:update',
            'flow_stage_required_columns:delete',

            'flow_items:read',
            'flow_items:create',
            'flow_items:update',
            'flow_items:delete',

            'flow_item_values:read',
            'flow_item_values:create',
            'flow_item_values:update',
            'flow_item_values:delete',

            'flow_stage_history:read',
            'flow_stage_history:create',
            'flow_stage_history:update',
            'flow_stage_history:delete',

            'flow_automations:read',
            'flow_automations:create',
            'flow_automations:update',
            'flow_automations:delete',

            'flow_item_watchers:read',
            'flow_item_watchers:create',
            'flow_item_watchers:update',
            'flow_item_watchers:delete',

            // Perspectives (read-only cross-account views)
            'flow_pipelines_perspective:read',
            'flow_stages_perspective:read',
            'flow_items_perspective:read',
            'flow_columns_perspective:read',
            'flow_automations_perspective:read',
        ];
    }

    public function getLevel(): int
    {
        return self::LEVEL;
    }

    public function getDescription(): string
    {
        return self::DESCRIPTION;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function canBeApplied(mixed $column): bool
    {
        if (self::DB_PREFIX === '*') {
            return true;
        }

        if (Str::startsWith($column, self::DB_PREFIX)) {
            return true;
        }

        return false;
    }

    public function getDbPrefix()
    {
        return self::DB_PREFIX;
    }

    public function checkRules(Users $_users): bool
    {
        return true;
    }
}
