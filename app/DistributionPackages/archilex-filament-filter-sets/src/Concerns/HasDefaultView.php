<?php

namespace Archilex\AdvancedTables\Concerns;

use Archilex\AdvancedTables\Support\Config;
use Filament\Forms\Components\Field;
use Illuminate\Support\Str;

trait HasDefaultView
{
    public bool $defaultViewIsActive = true;

    public function tableInDefaultState(): bool
    {
        return $this->tableFiltersInDefaultState() &&
            $this->tableGroupingInDefaultState() &&
            $this->tableGroupingDirectionInDefaultState() &&
            $this->tableSortColumnInDefaultState() &&
            $this->tableSortDirectionInDefaultState() &&
            $this->toggledTableColumnsInDefaultState() &&
            $this->toggledTableColumnsInDefaultOrder();
    }

    public function tableFiltersInDefaultState(): bool
    {
        if (empty($this->getTable()->getFilters())) {
            return true;
        }

        $filters = $this->tableFilters;

        return $this->normalizeTableFilterValues($filters) === $this->getTableFiltersDefaultState();
    }

    public function tableGroupingInDefaultState(): bool
    {
        return $this->tableGrouping === $this->getDefaultTableGrouping();
    }

    public function tableGroupingDirectionInDefaultState(): bool
    {
        return $this->tableGroupingDirection === null;
    }

    public function tableSortColumnInDefaultState(): bool
    {
        return $this->tableSortColumn === $this->getDefaultTableSortColumn();
    }

    public function tableSortDirectionInDefaultState(): bool
    {
        return $this->tableSortDirection === $this->getDefaultTableSortDirection();
    }

    public function toggledTableColumnsInDefaultOrder(): bool
    {
        if (! session()->has($this->getOrderedTableColumnToggleFormStateSessionKey())) {
            return true;
        }

        if (empty($this->orderedToggledTableColumns)) {
            return true;
        }

        return $this->getDefaultToggledTableColumnsOrder() === $this->orderedToggledTableColumns;
    }

    public function toggledTableColumnsInDefaultState(): bool
    {
        if (! session()->has($this->getTableColumnToggleFormStateSessionKey())) {
            return true;
        }

        if (empty($this->toggledTableColumns)) {
            return true;
        }

        $defaultColumns = collect($this->getDefaultTableColumnToggleState())
            ->dot()
            ->sortKeys()
            ->toArray();

        $toggledColumns = collect($this->toggledTableColumns)
            ->dot()
            ->sortKeys()
            ->toArray();

        return $defaultColumns === $toggledColumns;
    }

    protected function normalizeTableFilterValues(array &$data): array
    {
        foreach ($data as &$value) {
            if (is_array($value)) {
                $this->normalizeTableFilterValues($value);
            } elseif ($value === '') {
                $value = null;
            } elseif ($value === 'null') {
                $value = null;
            } elseif ($value === 'false') {
                $value = false;
            } elseif ($value === 'true') {
                $value = true;
            }
        }

        return $data;
    }

    public function getTableFiltersDefaultState(): array
    {
        $defaultFilterState = [];

        foreach ($this->getTableFiltersForm()->getComponents() as $filter) {
            $name = $filter->getKey();

            foreach ($filter->getChildComponentContainer()->getFlatFields() as $index => $field) {
                if ($field instanceof Field) {
                    if (Str::afterLast($index, '.') === 'or_group') {
                        continue;
                    }

                    if (Str::afterLast($index, '.') === 'and_group') {
                        data_set($defaultFilterState[$name], Str::beforeLast($index, '.data.') . '.type', 'filter_group');
                        data_set($defaultFilterState[$name], $index, $field->getDefaultState());

                        continue;
                    }

                    $defaultFilterState[$name][$field->getName()] = $field->getDefaultState();
                }
            }
        }

        return $defaultFilterState;
    }

    public function hasDefaultView(): bool
    {
        return Config::favoritesBarHasDefaultView();
    }
}
