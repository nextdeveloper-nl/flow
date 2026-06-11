<?php

namespace NextDeveloper\Flow\Database\Models;

use NextDeveloper\Commons\Database\Traits\HasStates;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\Flow\Database\Observers\StageHistoryPerspectiveObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Database\Traits\HasObject;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\RunAsAdministrator;

/**
 * StageHistoryPerspective model.
 *
 * @package  NextDeveloper\Flow\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property integer $flow_item_id
 * @property integer $flow_pipeline_id
 * @property integer $from_stage_id
 * @property integer $to_stage_id
 * @property integer $moved_by_iam_user_id
 * @property \Carbon\Carbon $moved_at
 * @property string $from_stage_name
 * @property string $from_stage_color
 * @property string $to_stage_name
 * @property string $to_stage_color
 * @property string $moved_by_name
 */
class StageHistoryPerspective extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates, RunAsAdministrator, HasObject;

    public $timestamps = false;

    protected $table = 'flow_stage_history_perspective';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'flow_item_id',
            'flow_pipeline_id',
            'from_stage_id',
            'to_stage_id',
            'moved_by_iam_user_id',
            'moved_at',
            'from_stage_name',
            'from_stage_color',
            'to_stage_name',
            'to_stage_color',
            'moved_by_name',
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
    'flow_item_id' => 'integer',
    'flow_pipeline_id' => 'integer',
    'from_stage_id' => 'integer',
    'to_stage_id' => 'integer',
    'moved_by_iam_user_id' => 'integer',
    'moved_at' => 'datetime',
    'from_stage_name' => 'string',
    'from_stage_color' => 'string',
    'to_stage_name' => 'string',
    'to_stage_color' => 'string',
    'moved_by_name' => 'string',
    ];

    /**
     We are casting data fields.
     *
     @var array
     */
    protected $dates = [
    'moved_at',
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
        parent::observe(StageHistoryPerspectiveObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('flow.scopes.global');
        $modelScopes = config('flow.scopes.flow_stage_history_perspective');

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
