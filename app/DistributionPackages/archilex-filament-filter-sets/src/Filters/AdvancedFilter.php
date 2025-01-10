<?php

namespace Archilex\AdvancedTables\Filters;

use Archilex\AdvancedTables\Filters\Concerns\CanIncludeColumns;
use Archilex\AdvancedTables\Filters\Concerns\HasFilterPicker;
use Archilex\AdvancedTables\Filters\Concerns\HasFiltersLayout;
use Archilex\AdvancedTables\Filters\Concerns\HasIcons;
use Archilex\AdvancedTables\Filters\Concerns\HasIndicators;
use Archilex\AdvancedTables\Filters\Concerns\HasOrGroups;
use Archilex\AdvancedTables\Forms\Components\AdvancedFilterBuilder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Field;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AdvancedFilter extends BaseFilter
{
    use CanIncludeColumns;
    use HasFilterPicker;
    use HasFiltersLayout;
    use HasIcons;
    use HasIndicators;
    use HasOrGroups;

    /**
     * @var array<string, BaseFilter>
     */
    protected array $filters = [];

    protected ?array $defaultFilters = null;

    protected array $modifiedQuery = [];

    protected int|string|null $liveDebounce = null;

    protected bool $isLiveOnBlur = false;

    public static function getDefaultName(): ?string
    {
        return 'advanced_filter_builder';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->baseQuery(function (Builder $query, array $data) {
            if (empty($data)) {
                return $query;
            }

            $this->applyFilterGroups($query, $data, true);
        });

        $this->query(function (Builder $query, array $data) {
            if (empty($data)) {
                return $query;
            }

            return $query->where(
                fn (Builder $query) => $this->applyFilterGroups($query, $data)
            );
        });

        $this->indicateUsing(function (AdvancedFilter $filter, array $state): array {
            $filterIndicators = $this->getFilterIndicators($filter, $state);

            if (! $this->hasOrGroups()) {
                return $filterIndicators;
            }

            $indicatorGroups = collect($filterIndicators)->groupBy(function (string $indicator, string $group) {
                return Str::of($group)->between('or_group', 'and_group');
            }, true)
                ->values();

            return $indicatorGroups->map(function ($indicators, $index) use ($indicatorGroups) {
                return collect($indicators)->map(function ($indicator, $field) use ($indicatorGroups, $index) {
                    if ($this->shouldPrependFilterGroup($indicatorGroups->count())) {
                        $indicator = __('advanced-tables::filter-builder.filters.indicator_name').' '.$index + 1 .' - '.$indicator;
                    }

                    return Indicator::make($indicator)->removeField($field)->color($this->getIndicatorColors()[$index]);
                });
            })
                ->flatten()
                ->toArray();
        });
    }

    public function getCollectedFilters(): array
    {
        $filterSort = $this->getFilterPickerFilterSort();

        if (! $this->mapColumns) {
            return collect($this->getFilters())
                ->reject(function (BaseFilter $filter) {
                    return $this->isColumnFilter($filter);
                })
                ->reject(function ($filter, $name) {
                    return $filter->isHidden();
                })
                ->when(is_bool($filterSort) && $filterSort, function (Collection $filters) {
                    return $filters->sortBy(fn (BaseFilter $filter) => $filter->getLabel());
                })
                ->toArray();
        }

        $tableColumns = $this->getTable()->getColumns();

        return collect($this->buildFilters($this->getFilters()))
            ->reject(function ($filter, $name) use ($tableColumns) {
                return
                    $this->isColumnFilter($filter) &&
                    ! Arr::exists($tableColumns, $name);
            })
            ->reject(function ($filter, $name) {
                return $filter->isHidden();
            })
            ->when(is_bool($filterSort) && $filterSort, function (Collection $filters) {
                return $filters->sortBy(fn (BaseFilter $filter) => $filter->getLabel());
            })
            ->when(is_bool($filterSort) && ! $filterSort, function (Collection $filters) use ($tableColumns) {
                return collect($tableColumns)
                    ->intersectByKeys($filters)
                    ->merge($filters);
            })
            ->when(is_array($filterSort), function (Collection $filters) use ($tableColumns, $filterSort) {
                $columns = collect($tableColumns)
                    ->intersectByKeys($filters);

                return collect($filterSort)
                    ->flip()
                    ->intersectByKeys($filters)
                    ->merge($columns)
                    ->merge($filters);
            })
            ->toArray();
    }

    public function getFormField(): Field
    {
        return AdvancedFilterBuilder::make('or_group')
            ->hiddenLabel()
            ->blocks([
                Block::make('filter_group')
                    ->schema([
                        AdvancedFilterBuilder::make('and_group')
                            ->hiddenLabel()
                            ->schema($this->getBlocks())
                            ->live(onBlur: $this->isLiveOnBlur(), debounce: $this->getLiveDebounce())
                            ->blockPickerColumns($this->getFilterPickerColumns())
                            ->blockPickerWidth($this->getFilterPickerWidth())
                            ->blockPickerMaxHeight($this->getFilterPickerMaxHeight())
                            ->blockPickerSearch($this->filterPickerHasSearch())
                            ->blockNumbers(false)
                            ->reorderable(false)
                            ->orGroups($this->hasOrGroups())
                            ->addActionLabel(__('advanced-tables::filter-builder.form.add_filter')),
                    ]),
            ])
            ->defaultFilters($this->getDefaultFilters())
            ->reorderable(false)
            ->orGroups($this->hasOrGroups())
            ->addActionLabel(__('advanced-tables::filter-builder.form.new_filter_group'));
    }

    /**
     * @param  array<BaseFilter>  $filters
     */
    public function filters(array $filters): static
    {
        foreach ($filters as $filter) {
            $this->filters[$filter->getName()] = $filter;
        }

        return $this;
    }

    public function defaultFilters(array $filters): static
    {
        $this->defaultFilters = $filters;

        return $this;
    }

    public function live(bool $onBlur = false, int|string|null $debounce = null): static
    {
        $this->isLiveOnBlur = $onBlur;
        $this->liveDebounce = $debounce;

        return $this;
    }

    /**
     * @return array<string, BaseFilter>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getDefaultFilters(): array
    {
        $filters = array_filter(
            $this->filters,
            fn (BaseFilter $filter): bool => $filter->isVisible(),
        );

        if (is_null($this->defaultFilters) && empty($filters)) {
            return [];
        }

        if (is_null($this->defaultFilters) && filled($filters)) {
            $filters = [
                array_keys($filters),
            ];

            if ($this->hasOrGroups()) {
                $filters[] = [];
            }

            return $filters;
        }

        $collectedFilters = $this->getCollectedFilters();

        $defaultFilterArray = ! is_null($this->defaultFilters)
            ? $this->defaultFilters
            : [array_keys($collectedFilters)];

        $defaultFilters = [];

        foreach ($defaultFilterArray as $filters) {
            $defaultFilters[] = array_filter(
                $filters,
                fn (string $filter): bool => array_key_exists($filter, $collectedFilters)
            );
        }

        if ($this->hasOrGroups()) {
            $defaultFilters[] = [];
        }

        return $defaultFilters;
    }

    public function getBlocks(): array
    {
        $blocks = [];

        $icons = $this->getIcons();

        foreach ($this->getCollectedFilters() as $name => $filter) {
            $blocks[] = Block::make($name)
                ->label(strip_tags($filter->getLabel()))
                ->icon(Arr::get($icons, $name, null))
                ->schema($filter->getFormSchema());
        }

        return $blocks;
    }

    public function isLiveOnBlur(): bool
    {
        return $this->isLiveOnBlur;
    }

    public function getLiveDebounce(): int|string|null
    {
        return $this->liveDebounce;
    }

    protected function applyFilterGroups(Builder $query, array $data, bool $applyToBaseQuery = false): void
    {
        $modifiedBaseQuery = [];

        $collectedFilters = $this->getCollectedFilters();

        foreach ($data['or_group'] ?? [] as $filterGroup) {
            if (! $applyToBaseQuery) {
                $this->applyFilterGroup($query, $filterGroup);

                continue;
            }

            foreach ($filterGroup['data'] ?? [] as $filters) {
                if (! $filters) {
                    continue;
                }

                $filters = array_filter($filters, function ($filter) use ($collectedFilters) {
                    return Arr::exists($collectedFilters, $filter['type'] ?? []);
                });

                if (empty($filters)) {
                    continue;
                }

                foreach ($filters as $index => $filter) {
                    $filterToApply = $collectedFilters[$filter['type']];

                    if ($filterToApply->isHidden()) {
                        continue;
                    }

                    if (! $filterToApply->hasBaseQueryModificationCallback()) {
                        continue;
                    }

                    if (! ($filter['data']['isActive'] ?? true)) {
                        continue;
                    }

                    if (in_array($filterToApply->getName(), $modifiedBaseQuery)) {
                        continue;
                    }

                    if (
                        $this->isColumnFilter($filterToApply) &&
                        $this->aggregatesRelationship($filterToApply->getColumn())
                    ) {
                        $filterToApply->applyAggregateToBaseQuery($query, $index, $filter['data'] ?? []);

                        continue;
                    }

                    $filterToApply->applyToBaseQuery($query, $filter['data'] ?? []);

                    $modifiedBaseQuery[] = $filterToApply->getName();
                }
            }
        }
    }

    protected function applyFilterGroup(Builder $query, array $filterGroup): void
    {
        $query->orWhere(function ($query) use ($filterGroup) {
            $collectedFilters = $this->getCollectedFilters();

            foreach ($filterGroup['data'] ?? [] as $filters) {
                if (! $filters) {
                    continue;
                }

                $filters = array_filter($filters, function ($filter) use ($collectedFilters) {
                    return Arr::exists($collectedFilters, $filter['type'] ?? []);
                });

                if (! empty($filters)) {
                    $this->applyFilters($query, $filters);
                }
            }
        });
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $collectedFilters = $this->getCollectedFilters();

        foreach ($filters as $index => $filter) {
            $filterToApply = $collectedFilters[$filter['type']];

            if (
                $this->isColumnFilter($filterToApply) &&
                $this->aggregatesRelationship($filterToApply->getColumn())
            ) {
                $filterToApply->applyAggregateToQuery($query, $index, $filter['data'] ?? []);

                continue;
            }

            $filterToApply->apply($query, $filter['data'] ?? []);
        }
    }

    protected function buildFilters(array $filters): array
    {
        foreach ($this->getTable()->getColumns() as $name => $column) {
            if (! $this->columnIsIncluded($name)) {
                continue;
            }

            if (
                Arr::exists($filters, $name) &&
                $filters[$name]->isHidden()
            ) {
                continue;
            }

            if (
                Arr::exists($filters, $name) &&
                $this->isColumnFilter($filters[$name])
            ) {
                $filters[$name] = $filters[$name]
                    ->column($column);

                continue;
            }

            if (Arr::exists($filters, $name)) {
                $filters[$name] = $filters[$name]
                    ->table($this->getTable());

                continue;
            }

            if ($column instanceof TextColumn) {
                $filters[$name] = $this->getTextColumnFilter($column);

                continue;
            }

            if ($column instanceof SelectColumn) {
                $filters[$name] = SelectFilter::make($column->getName())
                    ->column($column)
                    ->label(strip_tags($column->getLabel()))
                    ->options($column->getOptions());

                continue;
            }

            if (
                $column instanceof CheckboxColumn ||
                $column instanceof ToggleColumn ||
                $column instanceof ImageColumn ||
                ($column instanceof IconColumn && $column->isBoolean())
            ) {
                $filters[$name] = BooleanFilter::make($column->getName())
                    ->label(strip_tags($column->getLabel()));

                continue;
            }
        }

        return $filters;
    }

    protected function getTextColumnFilter(Column $column): BaseFilter
    {
        if ($this->aggregatesRelationship($column)) {
            return NumericFilter::make($column->getName())
                ->column($column)
                ->label(strip_tags($column->getLabel()));
        }

        $columnType = $this->getColumnType($column);

        if ($columnType === 'numeric') {
            return NumericFilter::make($column->getName())
                ->column($column)
                ->label(strip_tags($column->getLabel()));
        }

        if ($columnType === 'date') {
            return DateFilter::make($column->getName())
                ->column($column)
                ->label(strip_tags($column->getLabel()));
        }

        return TextFilter::make($column->getName())
            ->column($column)
            ->label(strip_tags($column->getLabel()));
    }

    protected function getColumnType(Column $column): string
    {
        if ($column->isNumeric() || $column->isMoney()) {
            return 'numeric';
        }

        if ($column->isDate() || $column->isDateTime()) {
            return 'date';
        }

        return 'text';
    }

    protected function isColumnFilter($filter): bool
    {
        return
            $filter instanceof TextFilter ||
            $filter instanceof NumericFilter ||
            $filter instanceof DateFilter ||
            $filter instanceof SelectFilter;
    }

    protected function aggregatesRelationship(Column $column): bool
    {
        return
            filled($column->getRelationshipToAvg()) ||
            filled($column->getRelationshipsToCount()) ||
            filled($column->getRelationshipToMax()) ||
            filled($column->getRelationshipToMin()) ||
            filled($column->getRelationshipToSum());
    }
}
