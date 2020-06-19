<?php

namespace Diskominfotik\QueryFilter\Traits;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Database\Eloquent\Builder;

trait HasQueryFilter
{
    /**
     * Get searchable properties
     *
     * @return Array
     */
    public function getSearchable()
    {
        return $this->searchable;
    }

    /**
     * Get relation can searchable properties
     *
     * @return Array
     */
    public function getRelationCanSearchable()
    {
        return $this->relationCanSearchable;
    }

    /**
     * Get filtering data
     *
     * @param builder $query
     * @return buider
     */
    public function getFilterData(Builder $query = null)
    {
        $filter = config('query-filter.filter_class');
        $builder = $query ? $query : $this->query();
        return app(Pipeline::class)
            ->send($builder)
            ->through($filter)
            ->thenReturn();
    }

    /**
     * Get data without paging
     *
     * @return Array|Collections
     */
    public function getAllData(Builder $query = null)
    {
        return $this->getFilterData($query)->get();
    }

    /**
     * Get data with paging
     *
     * @return Array|Collections
     */
    public function getPagingData(Builder $query = null)
    {
        $length = request()->has(config('query-filter.key.limit'))
            ? request(config('query-filter.key.limit'))
            : $this->perPage;

        return $this->getFilterData($query)->paginate($length)->appends(request()->input());
    }
}
