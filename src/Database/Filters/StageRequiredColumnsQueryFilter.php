<?php

namespace NextDeveloper\Flow\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
        

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class StageRequiredColumnsQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;

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
    
    public function flowColumnId($value)
    {
            $flowColumn = \NextDeveloper\Flow\Database\Models\Columns::where('uuid', $value)->first();

        if($flowColumn) {
            return $this->builder->where('flow_column_id', '=', $flowColumn->id);
        }
    }

        //  This is an alias function of flowColumn
    public function flow_column_id($value)
    {
        return $this->flowColumn($value);
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
