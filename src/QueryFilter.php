<?php

namespace Diskominfotik\QueryFilter;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

abstract class QueryFilter
{
    /**
     * Handle query
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param Closure $next
     * @return Closure
     */
    public function handle(Builder $builder, Closure $next)
    {
        if (request()->has($this->filterName()) && strlen(request($this->filterName())) > 0) {
            return $this->applyFilter($next($builder));
        }
        return $next($builder);
    }

    /**
     * Abstract function for processing closure
     *
     * @param Builder $builder
     * @return Closure
     */
    public abstract function applyFilter(Builder $builder);

    /**
     * Get filter name
     *
     * @return String
     */
    public abstract function filterName();
}
