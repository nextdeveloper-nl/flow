<?php

namespace NextDeveloper\Flow\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
                    

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class StageHistoryPerspectiveQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;
    
    public function fromStageName($value)
    {
        return $this->builder->where('from_stage_name', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of fromStageName
    public function from_stage_name($value)
    {
        return $this->fromStageName($value);
    }
        
    public function fromStageColor($value)
    {
        return $this->builder->where('from_stage_color', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of fromStageColor
    public function from_stage_color($value)
    {
        return $this->fromStageColor($value);
    }
        
    public function toStageName($value)
    {
        return $this->builder->where('to_stage_name', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of toStageName
    public function to_stage_name($value)
    {
        return $this->toStageName($value);
    }
        
    public function toStageColor($value)
    {
        return $this->builder->where('to_stage_color', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of toStageColor
    public function to_stage_color($value)
    {
        return $this->toStageColor($value);
    }
        
    public function movedByName($value)
    {
        return $this->builder->where('moved_by_name', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of movedByName
    public function moved_by_name($value)
    {
        return $this->movedByName($value);
    }
    
    public function movedAtStart($date)
    {
        return $this->builder->where('moved_at', '>=', $date);
    }

    public function movedAtEnd($date)
    {
        return $this->builder->where('moved_at', '<=', $date);
    }

    //  This is an alias function of movedAt
    public function moved_at_start($value)
    {
        return $this->movedAtStart($value);
    }

    //  This is an alias function of movedAt
    public function moved_at_end($value)
    {
        return $this->movedAtEnd($value);
    }

    public function flowItemId($value)
    {
            $flowItem = \NextDeveloper\Flow\Database\Models\Items::where('uuid', $value)->first();

        if($flowItem) {
            return $this->builder->where('flow_item_id', '=', $flowItem->id);
        }
    }

        //  This is an alias function of flowItem
    public function flow_item_id($value)
    {
        return $this->flowItem($value);
    }
    
    public function flowPipelineId($value)
    {
            $flowPipeline = \NextDeveloper\Flow\Database\Models\Pipelines::where('uuid', $value)->first();

        if($flowPipeline) {
            return $this->builder->where('flow_pipeline_id', '=', $flowPipeline->id);
        }
    }

        //  This is an alias function of flowPipeline
    public function flow_pipeline_id($value)
    {
        return $this->flowPipeline($value);
    }
    
    public function fromStageId($value)
    {
            $fromStage = \NextDeveloper\Flow\Database\Models\Stages::where('uuid', $value)->first();

        if($fromStage) {
            return $this->builder->where('from_stage_id', '=', $fromStage->id);
        }
    }

        //  This is an alias function of fromStage
    public function from_stage_id($value)
    {
        return $this->fromStage($value);
    }
    
    public function toStageId($value)
    {
            $toStage = \NextDeveloper\Flow\Database\Models\Stages::where('uuid', $value)->first();

        if($toStage) {
            return $this->builder->where('to_stage_id', '=', $toStage->id);
        }
    }

        //  This is an alias function of toStage
    public function to_stage_id($value)
    {
        return $this->toStage($value);
    }
    
    public function movedByIamUserId($value)
    {
            $movedByIamUser = \NextDeveloper\IAM\Database\Models\Users::where('uuid', $value)->first();

        if($movedByIamUser) {
            return $this->builder->where('moved_by_iam_user_id', '=', $movedByIamUser->id);
        }
    }

        //  This is an alias function of movedByIamUser
    public function moved_by_iam_user_id($value)
    {
        return $this->movedByIamUser($value);
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE










}
