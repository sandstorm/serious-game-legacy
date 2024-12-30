<?php

namespace Archilex\AdvancedTables\Filters\Concerns;

use Archilex\AdvancedTables\Filters\AdvancedFilter;
use Closure;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Support\Arr;

trait HasIndicators
{
    protected array $indicatorColors = [];

    protected bool | Closure $shouldPrependFilterGroupLabels = true;

    protected bool | Closure $shouldPrependFilterGroupLabelWhenSoleGroup = true;

    public function indicatorColors(array $colors): static
    {
        $this->indicatorColors = $colors;

        return $this;
    }

    public function prependFilterGroupLabels(bool | Closure $condition = true, bool | Closure $prependFilterGroupLabelWhenSoleGroup = true): static
    {
        $this->shouldPrependFilterGroupLabels = $condition;

        $this->shouldPrependFilterGroupLabelWhenSoleGroup = $prependFilterGroupLabelWhenSoleGroup;

        return $this;
    }

    public function getIndicatorColors(): array
    {
        $defaultColors = collect(['primary', 'info', 'gray',  'success', 'danger', 'warning']);

        return collect($this->indicatorColors)
            ->merge($defaultColors->diff($this->indicatorColors)->all())
            ->toArray();
    }

    protected function shouldPrependFilterGroup(int $indicatorGroupCount): bool
    {
        if (! $this->hasOrGroups()) {
            return false;
        }

        if (! $this->shouldPrependFilterGroupLabels()) {
            return false;
        }

        if (! $this->shouldPrependFilterGroupLabelWhenSoleGroup() && $indicatorGroupCount === 1) {
            return false;
        }

        return true;
    }

    public function shouldPrependFilterGroupLabels(): bool
    {
        return $this->evaluate($this->shouldPrependFilterGroupLabels);
    }

    public function shouldPrependFilterGroupLabelWhenSoleGroup(): bool
    {
        return $this->evaluate($this->shouldPrependFilterGroupLabelWhenSoleGroup);
    }

    protected function getFilterIndicators(AdvancedFilter $filter, array $state): array
    {
        $indicators = [];

        $loop = 1;

        foreach ($state['or_group'] ?? [] as $filterGroupKey => $filterGroup) {
            $this->processFilterGroup($filterGroup, $filterGroupKey, $loop, $indicators);

            $loop++;
        }

        return Arr::dot($indicators);
    }

    protected function processFilterGroup(array $filterGroup, string $filterGroupKey, $loop, &$indicators): void
    {
        foreach ($filterGroup['data'] ?? [] as $filters) {
            if (! $filters) {
                continue;
            }

            foreach ($filters as $filterKey => $filter) {
                $this->processFilter($filter, $filterKey, $filterGroupKey, $loop, $indicators);
            }
        }
    }

    protected function processFilter(array $filter, string $filterKey, string $filterGroupKey, int $loop, &$indicators): void
    {
        $collectedFilters = $this->getCollectedFilters();

        if (! Arr::exists($collectedFilters, $filter['type'] ?? '')) {
            return;
        }

        $filterObject = $collectedFilters[$filter['type']];

        $filterObject->table($this->getTable());

        if (
            Arr::exists($tableColumns = $this->getTable()->getColumns(), $filter['type']) &&
            $this->isColumnFilter($filterObject)
        ) {
            $this->buildColumnFilterIndicator($filter, $filterObject, $tableColumns, $filterGroupKey, $filterKey, $loop, $indicators);

            return;
        }

        $this->buildFilterIndicators($filter, $filterObject, $tableColumns, $filterGroupKey, $filterKey, $loop, $indicators);
    }

    protected function buildColumnFilterIndicator(array $filter, BaseFilter $filterObject, array $columns, string $filterGroupKey, string $filterKey, int $loop, &$indicators): void
    {
        $filterIndicators = $filterObject
            ->column($columns[$filter['type']])
            ->evaluate($filterObject->indicateUsing, [
                'data' => $filter['data'],
                'state' => $filter['data'],
            ]);

        if (blank($filterIndicators)) {
            return;
        }

        $filterIndicators = Arr::wrap($filterIndicators);

        $value = $filterIndicators[array_key_first($filterIndicators)];

        $indicatorKey = "or_group.{$filterGroupKey}.data.and_group.{$filterKey}.data.operator";

        data_set($indicators, $indicatorKey, $value);
    }

    protected function buildFilterIndicators(array $filter, BaseFilter $filterObject, array $columns, string $filterGroupKey, string $filterKey, int $loop, &$indicators): void
    {
        $filterData = $filter['data'] ?? [];
        $filterIndicators = $this->getEvaluatedFilterIndicators($filterObject, $filterData);

        if (blank($filterIndicators)) {
            return;
        }

        $normalizedIndicators = $this->normalizeIndicators($filterIndicators);

        foreach ($filterData as $name => $value) {
            if (blank($value)) {
                continue;
            }

            if (Arr::exists($normalizedIndicators, $name)) {
                $indicator = $normalizedIndicators[$name];
                $indicatorKey = $this->getIndicatorKey($filterGroupKey, $filterKey, $name);
            } else {
                $indicator = $normalizedIndicators[array_key_first($normalizedIndicators)];
                $indicatorKey = $this->getIndicatorKey($filterGroupKey, $filterKey, $filterObject->getName());
            }

            data_set($indicators, $indicatorKey, $indicator->getLabel());
        }
    }

    protected function getEvaluatedFilterIndicators(BaseFilter $filterObject, array $filterData): Indicator | array | string | null
    {
        return $filterObject->evaluate($filterObject->indicateUsing, [
            'data' => $filterData,
            'state' => $filterData,
        ]);
    }

    protected function normalizeIndicators($filterIndicators): array
    {
        return collect(Arr::wrap($filterIndicators))
            ->mapWithKeys(function ($indicator, $key) {
                if ($indicator instanceof Indicator) {
                    return [$indicator->getRemoveField() => $indicator];
                }

                return [$key => Indicator::make($indicator)];
            })
            ->toArray();
    }

    protected function getIndicatorKey(string $filterGroupKey, string $filterKey, string $name): string
    {
        return "or_group.{$filterGroupKey}.data.and_group.{$filterKey}.data.{$name}";
    }
}
