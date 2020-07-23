<?php

namespace Diskominfotik\QueryFilter\Query;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Diskominfotik\QueryFilter\QueryFilter;

class Search extends QueryFilter
{
    /**
     * Processing query builder with search conditions
     *
     * @param Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function applyFilter(Builder $builder)
    {
        $searchText = request($this->filterName());
        $searchable = $builder->getModel()->getSearchable();
        $relationCanSeachable = $this->getRelationCanSearchable($builder);
        $builder = $builder->where(function($query) use ($searchable, $searchText, $relationCanSeachable) {
            $row = 0;
            if (!empty($searchable)) {
                foreach ($searchable as $value) {
                    $condition = $row === 0 ? 'where' : 'orWhere';
                    $query->$condition($value, 'like', '%' . $searchText . '%');
                    $row++;
                }
            }

            if (!empty($relationCanSeachable)) {
                $relationCount = 0;
                foreach ($relationCanSeachable as $value) {
                    $method = ($relationCount == 0 && empty($searchable)) ? "whereHas" : "orWhereHas";
                    $fields = $value->fields;
                    $query->$method($value->relation, function($deepQuery) use ($fields, $searchText) {
                        $deepRow = 0;
                        foreach ($fields as $field) {
                            $condition = $deepRow === 0 ? 'where' : 'orWhere';
                            $deepQuery->$condition($field, 'like', '%' . $searchText . '%');
                            $deepRow++;
                        }
                    });
                    $relationCount++;
                }
            }
        });
        return $builder;
    }

    /**
     * Get extend filter name by config
     *
     * @return String
     */
    public function filterName()
    {
        return config('query-filter.key.search');
    }

    /**
     * Get all possible relation can searchable
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return Object
     */
    protected function getRelationCanSearchable(Builder $builder)
    {
        $relationCanSearchable = $builder->getModel()->getRelationCanSearchable();
        return collect($relationCanSearchable)->map(function($relationSearchable) use ($builder) {
            $temp = explode("|", $relationSearchable);
            return (object)[
                "relation" => $temp[0],
                "fields" => (object)explode(",", $temp[1]),
            ];
        });
    }
}
