<?php

namespace Archilex\AdvancedTables\Filters\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait HasQueryColumn
{
    protected function getQueryColumn(Builder $query): string
    {
        $model = $query->getModel();

        if (
            $this->column->queriesRelationships($model)
        ) {
            return $this->column->getRelationship($model)->getRelated()->qualifyColumn(Str::afterLast($this->column->getName(), '.'));
        }

        return $model->qualifyColumn($this->column->getName());
    }
}
