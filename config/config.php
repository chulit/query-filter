<?php

/*
 * You can place your custom package configuration in here.
 */

return [
    'key' => [
        'search'    => 'search',
        'sort_by'   => 'sort_by',
        'sort_desc' => 'sort_desc',
        'where'     => 'where',
        'limit'     => 'limit',
    ],

    'filter_class' => [
        'search'    => Diskominfotik\QueryFilter\Query\Search::class,
        'sort_by'   => Diskominfotik\QueryFilter\Query\SortBy::class,
        'where'     => Diskominfotik\QueryFilter\Query\Where::class
    ],
];
