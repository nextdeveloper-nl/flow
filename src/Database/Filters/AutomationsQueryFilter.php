<?php

namespace NextDeveloper\Flow\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
                

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class AutomationsQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;

    public function trigger($value)
    {
        return $this->builder->where('trigger', 'ilike', '%' . $value . '%');
    }


    public function eventName($value)
    {
        return $this->builder->where('event_name', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of eventName
    public function event_name($value)
    {
        return $this->eventName($value);
    }

    public function isActive($value)
    {
        return $this->builder->where('is_active', $value);
    }

        //  This is an alias function of isActive
    public function is_active($value)
    {
        return $this->isActive($value);
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


    public function commonPusherId($value)
    {
            $commonPusher = \NextDeveloper\Commons\Database\Models\Pushers::where('uuid', $value)->first();

        if($commonPusher) {
            return $this->builder->where('common_pusher_id', '=', $commonPusher->id);
        }
    }

        //  This is an alias function of commonPusher
    public function common_pusher_id($value)
    {
        return $this->commonPusher($value);
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE











}
