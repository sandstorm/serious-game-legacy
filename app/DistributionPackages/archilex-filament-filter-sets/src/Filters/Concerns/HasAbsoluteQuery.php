<?php

namespace Archilex\AdvancedTables\Filters\Concerns;

use Archilex\AdvancedTables\Filters\Operators\DateOperator;
use Illuminate\Database\Eloquent\Builder;

trait HasAbsoluteQuery
{
    public function applyAbsoluteQuery(Builder $query, array $data, string $column): Builder
    {
        if (in_array($data['operator'], [DateOperator::IS_DATE])) {
            return $query->whereDate($column, $data['date_start']);
        }

        if (in_array($data['operator'], [DateOperator::BEFORE_DATE])) {
            return $query->where($column, '<', $data['date_start']);
        }

        if (in_array($data['operator'], [DateOperator::AFTER_DATE])) {
            return $query->where($column, '>', $data['date_start']);
        }

        return $query->whereBetween($column, [$data['date_start'], $data['date_end']]);
    }
}
