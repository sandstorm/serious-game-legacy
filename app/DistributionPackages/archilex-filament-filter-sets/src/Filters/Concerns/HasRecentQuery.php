<?php

namespace Archilex\AdvancedTables\Filters\Concerns;

use Archilex\AdvancedTables\Filters\Operators\DateOperator;
use Illuminate\Database\Eloquent\Builder;

trait HasRecentQuery
{
    protected function applyRecentQuery(Builder $query, array $data, string $column): Builder
    {
        if (in_array($data['operator'], [DateOperator::YESTERDAY])) {
            return $query->whereDate($column, now()->subDay()->toDateString());
        }

        if (in_array($data['operator'], [DateOperator::TODAY])) {
            return $query->whereDate($column, now()->toDateString());
        }

        return $query->whereDate($column, now()->addDay()->toDateString());
    }

    protected function isRecentQuery(array $data): bool
    {
        return in_array($data['operator'], [DateOperator::YESTERDAY, DateOperator::TODAY, DateOperator::TOMORROW]);
    }
}
