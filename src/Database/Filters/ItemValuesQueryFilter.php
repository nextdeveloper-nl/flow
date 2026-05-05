<?php

namespace NextDeveloper\Flow\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
        

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class ItemValuesQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;
    
    public function value($value)
    {
        return $this->builder->where('value', 'ilike', '%' . $value . '%');
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
