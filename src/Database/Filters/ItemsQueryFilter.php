<?php

namespace NextDeveloper\Flow\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
                

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class ItemsQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;
    
    public function objectType($value)
    {
        return $this->builder->where('object_type', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of objectType
    public function object_type($value)
    {
        return $this->objectType($value);
    }
    
    public function position($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('position', $operator, $value);
    }

    
    public function lastStageChangedAtStart($date)
    {
        return $this->builder->where('last_stage_changed_at', '>=', $date);
    }

    public function lastStageChangedAtEnd($date)
    {
        return $this->builder->where('last_stage_changed_at', '<=', $date);
    }

    //  This is an alias function of lastStageChangedAt
    public function last_stage_changed_at_start($value)
    {
        return $this->lastStageChangedAtStart($value);
    }

    //  This is an alias function of lastStageChangedAt
    public function last_stage_changed_at_end($value)
    {
        return $this->lastStageChangedAtEnd($value);
    }

    public function createdAtStart($date)
    {
        return $this->builder->where('created_at', '>=', $date);
    }

    public function createdAtEnd($date)
    {
        return $this->builder->where('created_at', '<=', $date);
    }

    //  This is an alias function of createdAt
    public function created_at_start($value)
    {
        return $this->createdAtStart($value);
    }

    //  This is an alias function of createdAt
    public function created_at_end($value)
    {
        return $this->createdAtEnd($value);
    }

    public function updatedAtStart($date)
    {
        return $this->builder->where('updated_at', '>=', $date);
    }

    public function updatedAtEnd($date)
    {
        return $this->builder->where('updated_at', '<=', $date);
    }

    //  This is an alias function of updatedAt
    public function updated_at_start($value)
    {
        return $this->updatedAtStart($value);
    }

    //  This is an alias function of updatedAt
    public function updated_at_end($value)
    {
        return $this->updatedAtEnd($value);
    }

    public function deletedAtStart($date)
    {
        return $this->builder->where('deleted_at', '>=', $date);
    }

    public function deletedAtEnd($date)
    {
        return $this->builder->where('deleted_at', '<=', $date);
    }

    //  This is an alias function of deletedAt
    public function deleted_at_start($value)
    {
        return $this->deletedAtStart($value);
    }

    //  This is an alias function of deletedAt
    public function deleted_at_end($value)
    {
        return $this->deletedAtEnd($value);
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
    
    public function flowStageId($value)
    {
            $flowStage = \NextDeveloper\Flow\Database\Models\Stages::where('uuid', $value)->first();

        if($flowStage) {
            return $this->builder->where('flow_stage_id', '=', $flowStage->id);
        }
    }

        //  This is an alias function of flowStage
    public function flow_stage_id($value)
    {
        return $this->flowStage($value);
    }
    
    public function iamAccountId($value)
    {
            $iamAccount = \NextDeveloper\IAM\Database\Models\Accounts::where('uuid', $value)->first();

        if($iamAccount) {
            return $this->builder->where('iam_account_id', '=', $iamAccount->id);
        }
    }

    
    public function iamUserId($value)
    {
            $iamUser = \NextDeveloper\IAM\Database\Models\Users::where('uuid', $value)->first();

        if($iamUser) {
            return $this->builder->where('iam_user_id', '=', $iamUser->id);
        }
    }

    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE








}
