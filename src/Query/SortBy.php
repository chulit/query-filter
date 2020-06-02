<?php

namespace Diskominfotik\QueryFilter\Query;

use Illuminate\Database\Eloquent\Builder;
use Diskominfotik\QueryFilter\QueryFilter;

class SortBy extends QueryFilter
{
    /**
     * Processing query builder with sort_by conditions
     *
     * @param Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function applyFilter(Builder $builder)
    {
        $relation = explode(".", request($this->filterName()));
        $alias = str_replace(".", "_", request($this->filterName()));
        if (count($relation) == 1) {
            return $builder->orderBy(array_pop($relation), $this->getDirection());
        } else {
            $field = array_pop($relation);
            [$model, $join] = $this->getJoined($builder);
            return $builder
                ->select(["$model->name.*", "$join->name.$field as $alias"])
                ->join(
                    $join->name,
                    "$join->primary",
                    "=",
                    "$model->name.$join->foreign"
                )->orderBy("$join->name.$field", $this->getDirection());
        }
    }

    /**
     * Get extend filter name by config
     *
     * @return String
     */
    public function filterName()
    {
        return config('query-filter.key.sort_by');
    }

    /**
     * Get sorting direction
     *
     * @return String
     */
    protected function getDirection()
    {
        $sortDesc = config('query-filter.key.sort_desc');
        return request()->has($sortDesc) ? request($sortDesc) : 'asc';
    }

    /**
     * Get join properties
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return Array
     */
    protected function getJoined(Builder $builder)
    {
        $orignalTable = [
            "name" => $builder->getModel()->getTable(),
            "primary" => $builder->getModel()->getKeyName(),
            "foreign" => $builder->getModel()->getForeignKey(),
        ];
        $sortby = explode(".", request($this->filterName()));
        $relationName = array_shift($sortby);
        $relationship = (array)$builder->getModel()->$relationName();
        $relations = [];
        foreach ($relationship as $key => $value) {
            if (strpos($key, 'foreignKey')) $relations['foreignKey'] = $value;
            if (strpos($key, 'localKey')) $relations['localKey'] = $value;
        }
        $relationTable = [
            "name" => $builder->getModel()->$relationName()->getRelated()->getTable(),
            "primary" => array_shift($relations),
            "foreign" => array_shift($relations),
        ];
        return [(object)$orignalTable, (object)$relationTable];
    }
}
