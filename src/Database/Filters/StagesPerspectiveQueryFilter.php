<?php

namespace NextDeveloper\Flow\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
        

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class StagesPerspectiveQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;
    
    public function name($value)
    {
        return $this->builder->where('name', 'ilike', '%' . $value . '%');
    }

        
    public function color($value)
    {
        return $this->builder->where('color', 'ilike', '%' . $value . '%');
    }

        
    public function pipelineName($value)
    {
        return $this->builder->where('pipeline_name', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of pipelineName
    public function pipeline_name($value)
    {
        return $this->pipelineName($value);
    }
        
    public function pipelineObjectType($value)
    {
        return $this->builder->where('pipeline_object_type', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of pipelineObjectType
    public function pipeline_object_type($value)
    {
        return $this->pipelineObjectType($value);
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

    
    public function probability($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('probability', $operator, $value);
    }

    
    public function slaDays($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('sla_days', $operator, $value);
    }

        //  This is an alias function of slaDays
    public function sla_days($value)
    {
        return $this->slaDays($value);
    }
    
    public function itemCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('item_count', $operator, $value);
    }

        //  This is an alias function of itemCount
    public function item_count($value)
    {
        return $this->itemCount($value);
    }
    
    public function slaBreachedCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('sla_breached_count', $operator, $value);
    }

        //  This is an alias function of slaBreachedCount
    public function sla_breached_count($value)
    {
        return $this->slaBreachedCount($value);
    }
    
    public function requiredColumnCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('required_column_count', $operator, $value);
    }

        //  This is an alias function of requiredColumnCount
    public function required_column_count($value)
    {
        return $this->requiredColumnCount($value);
    }
    
    public function isWon($value)
    {
        return $this->builder->where('is_won', $value);
    }

        //  This is an alias function of isWon
    public function is_won($value)
    {
        return $this->isWon($value);
    }
     
    public function isLost($value)
    {
        return $this->builder->where('is_lost', $value);
    }

        //  This is an alias function of isLost
    public function is_lost($value)
    {
        return $this->isLost($value);
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
    
    public function pipelineIamAccountId($value)
    {
            $pipelineIamAccount = \NextDeveloper\IAM\Database\Models\Accounts::where('uuid', $value)->first();

        if($pipelineIamAccount) {
            return $this->builder->where('pipeline_iam_account_id', '=', $pipelineIamAccount->id);
        }
    }

        //  This is an alias function of pipelineIamAccount
    public function pipeline_iam_account_id($value)
    {
        return $this->pipelineIamAccount($value);
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
