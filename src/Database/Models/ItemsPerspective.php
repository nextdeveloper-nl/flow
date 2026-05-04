<?php

namespace NextDeveloper\Flow\Database\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use NextDeveloper\Commons\Database\Traits\HasStates;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\Flow\Database\Observers\ItemsPerspectiveObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Database\Traits\HasObject;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\RunAsAdministrator;

/**
 * ItemsPerspective model.
 *
 * @package  NextDeveloper\Flow\Database\Models
 * @property integer $id
 * @property integer $flow_pipeline_id
 * @property integer $flow_stage_id
 * @property string $object_type
 * @property integer $object_id
 * @property integer $position
 * @property \Carbon\Carbon $last_stage_changed_at
 * @property $checklist_state
 * @property integer $assigned_iam_user_id
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property string $stage_name
 * @property string $stage_color
 * @property integer $stage_sla_days
 * @property string $object_name
 * @property string $object_subtitle
 * @property $object_value
 * @property boolean $sla_breached
 */
class ItemsPerspective extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates, RunAsAdministrator, HasObject;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'flow_items_perspective';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'flow_pipeline_id',
            'flow_stage_id',
            'object_type',
            'object_id',
            'position',
            'last_stage_changed_at',
            'checklist_state',
            'assigned_iam_user_id',
            'iam_account_id',
            'iam_user_id',
            'stage_name',
            'stage_color',
            'stage_sla_days',
            'object_name',
            'object_subtitle',
            'object_value',
            'sla_breached',
    ];

    /**
      Here we have the fulltext fields. We can use these for fulltext search if enabled.
     */
    protected $fullTextFields = [

    ];

    /**
     @var array
     */
    protected $appends = [

    ];

    /**
     We are casting fields to objects so that we can work on them better
     *
     @var array
     */
    protected $casts = [
    'id' => 'integer',
    'flow_pipeline_id' => 'integer',
    'flow_stage_id' => 'integer',
    'object_type' => 'string',
    'object_id' => 'integer',
    'position' => 'integer',
    'last_stage_changed_at' => 'datetime',
    'checklist_state' => 'array',
    'assigned_iam_user_id' => 'integer',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'stage_name' => 'string',
    'stage_color' => 'string',
    'stage_sla_days' => 'integer',
    'object_name' => 'string',
    'object_subtitle' => 'string',
    'sla_breached' => 'boolean',
    ];

    /**
     We are casting data fields.
     *
     @var array
     */
    protected $dates = [
    'last_stage_changed_at',
    'created_at',
    'updated_at',
    'deleted_at',
    ];

    /**
     @var array
     */
    protected $with = [

    ];

    /**
     @var int
     */
    protected $perPage = 20;

    /**
     @return void
     */
    public static function boot()
    {
        parent::boot();

        //  We create and add Observer even if we wont use it.
        parent::observe(ItemsPerspectiveObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('flow.scopes.global');
        $modelScopes = config('flow.scopes.flow_items_perspective');

        if(!$modelScopes) { $modelScopes = [];
        }
        if (!$globalScopes) { $globalScopes = [];
        }

        $scopes = array_merge(
            $globalScopes,
            $modelScopes
        );

        if($scopes) {
            foreach ($scopes as $scope) {
                static::addGlobalScope(app($scope));
            }
        }
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
