<?php

namespace NextDeveloper\Flow\Authorization\Roles;

use Illuminate\Database\Eloquent\Model;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * Shared by Flow role classes to delegate authorization on a flow_items row
 * to the object it links to (object_type/object_id) instead of the row's
 * own ownership, when that linked object can be resolved.
 */
trait AuthorizesLinkedObject
{
    /**
     * Returns the permission result for the linked object, or null when
     * there's no resolvable linked object (caller should fall back to
     * row-ownership checks in that case).
     */
    protected function getLinkedObjectPermission(Model $model, string $method): ?bool
    {
        if ($model->getTable() !== 'flow_items') {
            return null;
        }

        if (!method_exists($model, 'getObject') || !$model->object_type || !$model->object_id) {
            return null;
        }

        $linkedObject = $model->getObject();

        if (!$linkedObject) {
            return null;
        }

        return UserHelper::can($method, $linkedObject);
    }
}
