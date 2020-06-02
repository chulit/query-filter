<?php

namespace Diskominfotik\QueryFilter\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Diskominfotik\QueryFilter\QueryFilter
 */
class QueryFilter extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'query-filter';
    }
}
