<?php

namespace Diskominfotik\QueryFilter\Query;

use Illuminate\Database\Eloquent\Builder;
use Diskominfotik\QueryFilter\QueryFilter;

class Where extends QueryFilter
{
    /**
     * Processing query builder with where conditions
     *
     * @param Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function applyFilter(Builder $builder) {
        [$field, $value] = $this->getCondition();
        return $builder->where($field, $value);
    }

    /**
     * Get extend filter name by config
     *
     * @return String
     */
    public function filterName()
    {
        return config('query-filter.key.where');
    }

    /**
     * Get and transform where condition
     *
     * @return Array
     */
    protected function getCondition()
    {
        $condition = explode("|", request($this->filterName()));
        return [$condition[0], $condition[1]];
    }
}
