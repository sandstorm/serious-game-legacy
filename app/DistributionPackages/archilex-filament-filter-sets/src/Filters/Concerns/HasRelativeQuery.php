<?php

namespace Archilex\AdvancedTables\Filters\Concerns;

use Archilex\AdvancedTables\Filters\Operators\DateOperator;
use Archilex\AdvancedTables\Filters\Units\DateUnit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait HasRelativeQuery
{
    public function applyRelativeQuery(Builder $query, array $data, string $column): Builder
    {
        if (in_array($data['operator'], [DateOperator::IN_THIS])) {
            return $this->applyRelativeInThisQuery($query, $data, $column);
        }

        if (in_array($data['operator'], [DateOperator::IS_NEXT])) {
            return $this->applyRelativeIsNextQuery($query, $data, $column);
        }

        if (in_array($data['operator'], [DateOperator::IS_LAST])) {
            return $this->applyRelativeIsLastQuery($query, $data, $column);
        }

        if (in_array($data['operator'], [DateOperator::IN_THE_NEXT])) {
            return $this->applyRelativeInTheNextQuery($query, $data, $column);
        }

        if (in_array($data['operator'], [DateOperator::IN_THE_LAST])) {
            return $this->applyRelativeInTheLastQuery($query, $data, $column);
        }

        if (in_array($data['operator'], [DateOperator::EXACTLY, DateOperator::BEFORE, DateOperator::AFTER])) {
            return $this->applyRelativeExactlyBeforeAfterQuery($query, $data, $column);
        }

        if (in_array($data['operator'], [DateOperator::BETWEEN])) {
            return $this->applyRelativeBetweenQuery($query, $data, $column);
        }
    }

    protected function applyRelativeInThisQuery(Builder $query, array $data, string $column): Builder
    {
        $start = match ($data['unit']) {
            DateUnit::WEEK => 'startOfWeek',
            DateUnit::MONTH => 'startOfMonth',
            DateUnit::QUARTER => 'startOfQuarter',
            DateUnit::YEAR => 'startOfYear',
            default => false,
        };

        $end = match ($data['unit']) {
            DateUnit::WEEK => 'endOfWeek',
            DateUnit::MONTH => 'endOfMonth',
            DateUnit::QUARTER => 'endOfQuarter',
            DateUnit::YEAR => 'endOfYear',
            default => false,
        };

        return $query->when(
            $start && $end,
            fn ($query) => $query->whereBetween($column, [now()->{$start}(), now()->{$end}()])
        );
    }

    protected function applyRelativeIsNextQuery(Builder $query, array $data, string $column): Builder
    {
        $start = match ($data['unit']) {
            DateUnit::WEEK => now()->startOfWeek()->addWeek(),
            DateUnit::MONTH => now()->startOfMonth()->addMonthNoOverflow(),
            DateUnit::QUARTER => now()->startOfQuarter()->addQuarterNoOverflow(),
            DateUnit::YEAR => now()->startOfYear()->addYearNoOverflow(),
            default => false,
        };

        $end = match ($data['unit']) {
            DateUnit::WEEK => now()->endOfWeek()->addWeek(),
            DateUnit::MONTH => now()->endOfMonth()->addMonthNoOverflow(),
            DateUnit::QUARTER => now()->endOfQuarter()->addQuarterNoOverflow(),
            DateUnit::YEAR => now()->endOfYear()->addYearNoOverflow(),
            default => false,
        };

        return $query->when(
            $start && $end,
            fn ($query) => $query->whereBetween($column, [$start, $end])
        );
    }

    protected function applyRelativeIsLastQuery(Builder $query, array $data, string $column): Builder
    {
        $start = match ($data['unit']) {
            DateUnit::WEEK => now()->startOfWeek()->subWeek(),
            DateUnit::MONTH => now()->startOfMonth()->subMonthNoOverflow(),
            DateUnit::QUARTER => now()->startOfQuarter()->subQuarterNoOverflow(),
            DateUnit::YEAR => now()->startOfYear()->subYearNoOverflow(),
            default => false,
        };

        $end = match ($data['unit']) {
            DateUnit::WEEK => now()->endOfWeek()->subWeek(),
            DateUnit::MONTH => now()->endOfMonth()->subMonthNoOverflow(),
            DateUnit::QUARTER => now()->endOfQuarter()->subQuarterNoOverflow(),
            DateUnit::YEAR => now()->endOfYear()->subYearNoOverflow(),
            default => false,
        };

        return $query->when(
            $start && $end,
            fn ($query) => $query->whereBetween($column, [$start, $end])
        );
    }

    protected function applyRelativeInTheNextQuery(Builder $query, array $data, string $column): Builder
    {
        $end = match ($data['unit']) {
            DateUnit::DAYS => 'addDays',
            DateUnit::WEEKS => 'addWeeks',
            DateUnit::MONTHS => 'addMonths',
            DateUnit::QUARTERS => 'addQuarters',
            DateUnit::YEARS => 'addYears',
            default => false,
        };

        return $query->when(
            $end,
            fn ($query) => $query->whereBetween($column, [now(), now()->{$end}(intval($data['value']))])
        );
    }

    protected function applyRelativeInTheLastQuery(Builder $query, array $data, string $column): Builder
    {
        $start = match ($data['unit']) {
            DateUnit::DAYS => 'subDays',
            DateUnit::WEEKS => 'subWeeks',
            DateUnit::MONTHS => 'subMonths',
            DateUnit::QUARTERS => 'subQuarters',
            DateUnit::YEARS => 'subYears',
            default => false,
        };

        return $query->when(
            $start,
            fn ($query) => $query->whereBetween($column, [now()->$start($data['value']), now()])
        );
    }

    protected function applyRelativeExactlyBeforeAfterQuery(Builder $query, array $data, string $column): Builder
    {
        $operator = $data['operator'] === DateOperator::BEFORE
            ? '<'
            : '>';

        $unit = match ($data['unit']) {
            DateUnit::DAYS_AGO => 'subDays',
            DateUnit::DAYS_FROM_NOW => 'addDays',
            DateUnit::WEEKS_AGO => 'subWeeks',
            DateUnit::WEEKS_FROM_NOW => 'addWeeks',
            DateUnit::MONTHS_AGO => 'subMonths',
            DateUnit::MONTHS_FROM_NOW => 'addMonths',
            DateUnit::QUARTERS_AGO => 'subQuarters',
            DateUnit::QUARTERS_FROM_NOW => 'addQuarters',
            DateUnit::YEARS_AGO => 'subYears',
            DateUnit::YEARS_FROM_NOW => 'addYears',
            default => false,
        };

        return $query
            ->when(
                $unit && $data['operator'] === DateOperator::EXACTLY && $data['value'],
                fn ($query) => $query->whereDate($column, now()->{$unit}(intval($data['value'])))
            )
            ->when(
                $unit && in_array($data['operator'], [DateOperator::BEFORE, DateOperator::AFTER]) && $data['value'],
                fn ($query) => $query->where($column, $operator, now()->{$unit}(intval($data['value'])))
            );
    }

    protected function applyRelativeBetweenQuery(Builder $query, array $data, string $column): Builder
    {
        $unit = match ($data['unit']) {
            DateUnit::DAYS_AGO => 'subDays',
            DateUnit::DAYS_FROM_NOW => 'addDays',
            DateUnit::WEEKS_AGO => 'subWeeks',
            DateUnit::WEEKS_FROM_NOW => 'addWeeks',
            DateUnit::MONTHS_AGO => 'subMonths',
            DateUnit::MONTHS_FROM_NOW => 'addMonths',
            DateUnit::QUARTERS_AGO => 'subQuarters',
            DateUnit::QUARTERS_FROM_NOW => 'addQuarters',
            DateUnit::YEARS_AGO => 'subYears',
            DateUnit::YEARS_FROM_NOW => 'addYears',
            default => false,
        };

        if (! $unit) {
            return $query;
        }

        $start = Str::after($unit, '_') === 'from_now'
            ? (int) $data['between_start']
            : (int) $data['between_end'];

        $end = Str::after($unit, '_') === 'from_now'
            ? (int) $data['between_end']
            : (int) $data['between_start'];

        $startDate = $start === 0
            ? now()
            : now()->{$unit}($start);

        $endDate = $end === 0
            ? now()
            : now()->{$unit}($end);

        return $query->when(
            $unit && $startDate && $endDate,
            fn ($query) => $query->whereBetween($column, [$startDate, $endDate])
        );
    }

    protected function isRelativeQuery(array $data): bool
    {
        return in_array($data['operator'], [DateOperator::IN_THIS, DateOperator::IS_NEXT, DateOperator::IS_LAST, DateOperator::IN_THE_NEXT, DateOperator::IN_THE_LAST, DateOperator::EXACTLY, DateOperator::AFTER, DateOperator::BEFORE, DateOperator::BETWEEN]);
    }
}
