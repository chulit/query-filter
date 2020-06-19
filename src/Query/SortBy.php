<?php

namespace Diskominfotik\QueryFilter\Query;

use Illuminate\Support\Str;
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
        $relation = explode('.', request($this->filterName()));
        if (count($relation) == 1) {
            return $builder->orderBy(array_pop($relation), $this->getDirection());
        } else {
            $field = array_pop($relation);
            return $this->getJoined($builder, $field);
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
    protected function getJoined(Builder $builder, String $field)
    {
        $sortby = explode(".", request($this->filterName()));
        $relationName = array_shift($sortby);
        $relationType = $this->getRelationType($builder, $relationName);
        $relationMapped = [
            'hasOneOrMany' => ['hasOne', 'hasMany'],
            'belongsTo' => ['belongsTo'],
            'belongsOrMorphToMany' => ['belongsToMany', 'morphToMany']
        ];

        $relationFunction = '';
        foreach ($relationMapped as $key => $value) {
            if (in_array($relationType, $value)) {
                $relationFunction = $key;
                break;
            }
        }
        return $this->$relationFunction($builder, $relationName, $field);
    }

    /**
     * Handle join two table only
     *
     * @param Builder $builder
     * @param String $table
     * @param String $joinTable
     * @param String $qualifiedTableKeyName
     * @param String $qualifiedJoinTableForeignKey
     * @param String $field
     * @return Builder
     */
    protected function joinTwoTables(
        Builder $builder,
        String $table,
        String $joinTable,
        String $qualifiedTableKeyName,
        String $qualifiedJoinTableForeignKey,
        String $field
    ) {
        return $builder
            ->select($table.'.*')
            ->leftJoin($joinTable, $qualifiedTableKeyName, '=', $qualifiedJoinTableForeignKey)
            ->orderBy("{$joinTable}.{$field}", $this->getDirection());
    }

    /**
     * Handle HasOne or HasMany relations
     *
     * @param Builder $builder
     * @param String $relationName
     * @param String $field
     * @return \Diskominfotik\QueryFilter\Query::joinTwoTables
     */
    protected function hasOneOrMany(Builder $builder, String $relationName, String $field)
    {
        $model = $builder->getModel();
        $table = $model->getTable();
        $joinTable = $model->$relationName()->getRelated()->getTable();
        $qualifiedTableKeyName = $model->$relationName()->getQualifiedParentKeyName();
        $qualifiedJoinTableForeignKey = $model->$relationName()->getQualifiedForeignKeyName();
        return $this->joinTwoTables(
            $builder,
            $table,
            $joinTable,
            $qualifiedTableKeyName,
            $qualifiedJoinTableForeignKey,
            $field
        );
    }

    /**
     * Handle BelongsTo (HasOne reverse) relations
     *
     * @param Builder $builder
     * @param String $relationName
     * @param String $field
     * @return \Diskominfotik\QueryFilter\Query::joinTwoTables
     */
    protected function belongsTo(Builder $builder, String $relationName, String $field)
    {
        $model = $builder->getModel();
        $table = $model->getTable();
        $joinTable = $model->$relationName()->getRelated()->getTable();
        $qualifiedTableKeyName = "{$table}.{$model->$relationName()->getForeignKeyName()}";
        $qualifiedJoinTableForeignKey = $model->$relationName()->getQualifiedOwnerKeyName();
        return $this->joinTwoTables(
            $builder,
            $table,
            $joinTable,
            $qualifiedTableKeyName,
            $qualifiedJoinTableForeignKey,
            $field
        );
    }

    /**
     * Handle BelongsToMany or MorphToMany relations
     *
     * @param Builder $builder
     * @param String $relationName
     * @param String $field
     * @return Builder
     */
    protected function belongsOrMorphToMany(Builder $builder, String $relationName, String $field)
    {
        $model = $builder->getModel();
        $table = $model->getTable();
        $relatedTable = $model->$relationName()->getRelated()->getTable();
        $pivotTable = $model->$relationName()->getTable();
        $qualifiedForeignPivotKeyName = $model->$relationName()->getQualifiedForeignPivotKeyName();
        $qualifiedRelatedPivotKeyName = $model->$relationName()->getQualifiedRelatedPivotKeyName();
        $qualifiedTableKeyName = $model->$relationName()->getQualifiedParentKeyName();
        $qualifiedRelatedKeyName = "{$relatedTable}.{$model->$relationName()->getRelatedKeyName()}";
        return $builder
            ->select($table . '.*')
            ->leftJoin($pivotTable, $qualifiedTableKeyName, '=', $qualifiedForeignPivotKeyName)
            ->leftJoin($relatedTable, $qualifiedRelatedKeyName, '=', $qualifiedRelatedPivotKeyName)
            ->orderBy("{$relatedTable}.{$field}", $this->getDirection());
    }

    /**
     * Get relation type
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param String $relationName
     * @return String
     */
    protected function getRelationType(Builder $builder, $relationName)
    {
        $className = get_class($builder->getModel());
        $obj = new $className;
        $type = get_class($obj->$relationName());
        $path = explode('\\', $type);
        return Str::camel(array_pop($path));
    }
}
