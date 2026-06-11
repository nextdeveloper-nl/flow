<?php

namespace NextDeveloper\Flow\Database\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use NextDeveloper\Commons\Database\Traits\HasStates;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\Flow\Database\Observers\StagesPerspectiveObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Database\Traits\HasObject;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\RunAsAdministrator;

/**
 * StagesPerspective model.
 *
 * @package  NextDeveloper\Flow\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property integer $flow_pipeline_id
 * @property string $name
 * @property string $color
 * @property integer $position
 * @property integer $probability
 * @property integer $sla_days
 * @property boolean $is_won
 * @property boolean $is_lost
 * @property $checklist
 * @property boolean $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property string $pipeline_name
 * @property string $pipeline_object_type
 * @property integer $pipeline_iam_account_id
 * @property integer $item_count
 * @property integer $sla_breached_count
 * @property $avg_days_in_stage
 * @property integer $required_column_count
 */
class StagesPerspective extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates, RunAsAdministrator, HasObject;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'flow_stages_perspective';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'flow_pipeline_id',
            'name',
            'color',
            'position',
            'probability',
            'sla_days',
            'is_won',
            'is_lost',
            'checklist',
            'is_active',
            'pipeline_name',
            'pipeline_object_type',
            'pipeline_iam_account_id',
            'item_count',
            'sla_breached_count',
            'avg_days_in_stage',
            'required_column_count',
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
    'name' => 'string',
    'color' => 'string',
    'position' => 'integer',
    'probability' => 'integer',
    'sla_days' => 'integer',
    'is_won' => 'boolean',
    'is_lost' => 'boolean',
    'checklist' => 'array',
    'is_active' => 'boolean',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'pipeline_name' => 'string',
    'pipeline_object_type' => 'string',
    'pipeline_iam_account_id' => 'integer',
    'item_count' => 'integer',
    'sla_breached_count' => 'integer',
    'required_column_count' => 'integer',
    ];

    /**
     We are casting data fields.
     *
     @var array
     */
    protected $dates = [
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
        parent::observe(StagesPerspectiveObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('flow.scopes.global');
        $modelScopes = config('flow.scopes.flow_stages_perspective');

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
