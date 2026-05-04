<?php

namespace NextDeveloper\Flow\Authorization\Roles;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Helpers\DatabaseHelper;
use NextDeveloper\IAM\Authorization\Roles\AbstractRole;
use NextDeveloper\IAM\Authorization\Roles\IAuthorizationRole;
use NextDeveloper\IAM\Database\Models\Users;
use NextDeveloper\IAM\Helpers\UserHelper;

class FlowUserRole extends AbstractRole implements IAuthorizationRole
{
    public const NAME = 'flow-user';

    public const LEVEL = 200;

    public const DESCRIPTION = 'Flow user with CRUD access limited to objects they personally own (iam_user_id).';

    public const DB_PREFIX = 'flow';

    private const PERSPECTIVES = [
        'flow_pipelines_perspective',
        'flow_stages_perspective',
        'flow_items_perspective',
        'flow_columns_perspective',
        'flow_automations_perspective',
    ];

    /**
     * Restricts queries to records owned by the current user.
     * Falls back to account scope for tables without iam_user_id.
     * Perspectives are not filtered — they provide their own scoping.
     */
    public function apply(Builder $builder, Model $model)
    {
        if (in_array($model->getTable(), self::PERSPECTIVES)) {
            return;
        }

        $hasUserId    = DatabaseHelper::isColumnExists($model->getTable(), 'iam_user_id');
        $hasAccountId = DatabaseHelper::isColumnExists($model->getTable(), 'iam_account_id');

        if ($hasUserId) {
            $builder->where('iam_user_id', UserHelper::me()->id);
            return;
        }

        if ($hasAccountId) {
            $builder->where('iam_account_id', UserHelper::currentAccount()->id);
        }
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

            // Perspectives (read-only)
            'flow_pipelines_perspective:read',
            'flow_stages_perspective:read',
            'flow_items_perspective:read',
            'flow_columns_perspective:read',
            'flow_automations_perspective:read',
        ];
    }

    /**
     * Users can only update records they personally own.
     * Falls back to account ownership for tables without iam_user_id.
     */
    public function checkUpdatePolicy(Model $model, Users $user): bool
    {
        if (UserHelper::hasRole('system-admin')) {
            return true;
        }

        $operation = $model->getTable() . ':update';

        if (in_array('!' . $operation, $this->allowedOperations())) {
            return true;
        }

        if (!in_array($operation, $this->allowedOperations())) {
            return false;
        }

        if (DatabaseHelper::isColumnExists($model->getTable(), 'iam_user_id')) {
            return $model->iam_user_id == UserHelper::me()->id;
        }

        if (DatabaseHelper::isColumnExists($model->getTable(), 'iam_account_id')) {
            return $model->iam_account_id == UserHelper::currentAccount()->id;
        }

        return true;
    }

    /**
     * Users can only delete records they personally own.
     * Falls back to account ownership for tables without iam_user_id.
     */
    public function checkDeletePolicy(Model $model, Users $user): bool
    {
        if (UserHelper::hasRole('system-admin')) {
            return true;
        }

        $operation = $model->getTable() . ':delete';

        if (in_array('!' . $operation, $this->allowedOperations())) {
            return true;
        }

        if (!in_array($operation, $this->allowedOperations())) {
            return false;
        }

        if (DatabaseHelper::isColumnExists($model->getTable(), 'iam_user_id')) {
            return $model->iam_user_id == UserHelper::me()->id;
        }

        if (DatabaseHelper::isColumnExists($model->getTable(), 'iam_account_id')) {
            return $model->iam_account_id == UserHelper::currentAccount()->id;
        }

        return true;
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
