<?php

namespace Archilex\AdvancedTables\Filters;

use Archilex\AdvancedTables\Filters\Concerns\HasAbsoluteQuery;
use Archilex\AdvancedTables\Filters\Concerns\HasColumn;
use Archilex\AdvancedTables\Filters\Concerns\HasFiltersLayout;
use Archilex\AdvancedTables\Filters\Concerns\HasOperators;
use Archilex\AdvancedTables\Filters\Concerns\HasQueryColumn;
use Archilex\AdvancedTables\Filters\Concerns\HasRecentQuery;
use Archilex\AdvancedTables\Filters\Concerns\HasRelativeQuery;
use Archilex\AdvancedTables\Filters\Operators\DateOperator;
use Archilex\AdvancedTables\Filters\Units\DateUnit;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component as Livewire;

class DateFilter extends BaseFilter
{
    use HasAbsoluteQuery;
    use HasColumn;
    use HasFiltersLayout;
    use HasOperators;
    use HasQueryColumn;
    use HasRecentQuery;
    use HasRelativeQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->indicateUsing(function (DateFilter $filter, array $state): array {
            if (! $this->formFilled($state)) {
                return [];
            }

            return $this->getFilterIndicator($state);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function apply(Builder $query, array $data = []): Builder
    {
        if (! $this->formFilled($data)) {
            return $query;
        }

        $column = $this->getQueryColumn($query);

        if (! $this->column->hasRelationship($query->getModel())) {
            return $this->applyQuery($query, $data, $column);
        }

        return $query->whereHas(
            $this->column->getRelationshipName(),
            fn (Builder $query) => $this->applyQuery($query, $data, $column)
        );
    }

    public function getFormSchema(): array
    {
        return [
            Grid::make()
                ->columns(['sm' => 4, 'lg' => 4])
                ->extraAttributes(['class' => 'advanced-tables-filter-block advanced-tables-filter-block-date-filter'])
                ->schema([
                    TextInput::make('column')
                        ->default($this->getName())
                        ->hidden(),
                    Select::make('operator')
                        ->label(strip_tags($this->getLabel()))
                        ->extraAttributes(['class' => 'advanced-tables-filter-operator advanced-tables-filter-operator-date-filter'])
                        ->live()
                        ->default($this->getDefaultOperator())
                        ->options($this->getOperatorOptions())
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                            if (
                                in_array($state, [DateOperator::IN_THIS, DateOperator::IS_NEXT, DateOperator::IS_LAST]) &&
                                ! in_array($get('unit'), [DateUnit::WEEK, DateUnit::MONTH, DateUnit::QUARTER, DateUnit::YEAR])
                            ) {
                                $set('unit', null);
                            }

                            if (
                                in_array($state, [DateOperator::IN_THE_NEXT, DateOperator::IN_THE_LAST]) &&
                                ! in_array($get('unit'), [DateUnit::DAYS, DateUnit::WEEKS, DateUnit::MONTHS, DateUnit::QUARTERS, DateUnit::YEARS])
                            ) {
                                $set('unit', null);
                            }

                            if (
                                in_array($state, [DateOperator::EXACTLY, DateOperator::BEFORE, DateOperator::AFTER, DateOperator::BETWEEN]) &&
                                ! in_array($get('unit'), [DateUnit::DAYS_AGO, DateUnit::DAYS_FROM_NOW, DateUnit::WEEKS_AGO, DateUnit::WEEKS_FROM_NOW, DateUnit::MONTHS_AGO, DateUnit::MONTHS_FROM_NOW, DateUnit::QUARTERS_AGO, DateUnit::QUARTERS_FROM_NOW, DateUnit::YEARS_AGO, DateUnit::YEARS_FROM_NOW])
                            ) {
                                $set('unit', null);
                            }
                        })
                        ->columnSpan([
                            'sm' => fn (Livewire $livewire) => $this->hasWideLayout($livewire) ? 1 : 4,
                        ]),
                    Grid::make()
                        ->columns(['default' => 4, 'sm' => 12, 'lg' => 12])
                        ->schema([
                            TextInput::make('value')
                                ->numeric()
                                ->minValue(1)
                                ->hiddenLabel()
                                ->columnSpan([
                                    'sm' => fn (Livewire $livewire) => $this->hasWideLayout($livewire) ? 3 : 3,
                                ])
                                ->visible(fn (Get $get) => in_array($get('operator'), [DateOperator::IN_THE_NEXT, DateOperator::IN_THE_LAST, DateOperator::EXACTLY, DateOperator::BEFORE, DateOperator::AFTER])),
                            TextInput::make('between_start')
                                ->numeric()
                                ->minValue(0)
                                ->hiddenLabel()
                                ->columnSpan([
                                    'default' => 2,
                                    'sm' => fn (Livewire $livewire) => $this->hasWideLayout($livewire) ? 3 : 6,
                                ])
                                ->visible(fn (Get $get) => in_array($get('operator'), [DateOperator::BETWEEN])),
                            TextInput::make('between_end')
                                ->numeric()
                                ->minValue(0)
                                ->hiddenLabel()
                                ->columnSpan([
                                    'default' => 2,
                                    'sm' => fn (Livewire $livewire) => $this->hasWideLayout($livewire) ? 3 : 6,
                                ])
                                ->visible(fn (Get $get) => in_array($get('operator'), [DateOperator::BETWEEN])),
                            Select::make('unit')
                                ->hiddenLabel()
                                ->options(function (Get $get) {
                                    if (in_array($get('operator'), [DateOperator::IN_THIS, DateOperator::IS_NEXT, DateOperator::IS_LAST])) {
                                        return [
                                            DateUnit::WEEK => __('advanced-tables::filter-builder.filters.date.unit.week.option'),
                                            DateUnit::MONTH => __('advanced-tables::filter-builder.filters.date.unit.month.option'),
                                            DateUnit::QUARTER => __('advanced-tables::filter-builder.filters.date.unit.quarter.option'),
                                            DateUnit::YEAR => __('advanced-tables::filter-builder.filters.date.unit.year.option'),
                                        ];
                                    }

                                    if (in_array($get('operator'), [DateOperator::IN_THE_NEXT, DateOperator::IN_THE_LAST])) {
                                        return [
                                            DateUnit::DAYS => __('advanced-tables::filter-builder.filters.date.unit.days.option'),
                                            DateUnit::WEEKS => __('advanced-tables::filter-builder.filters.date.unit.weeks.option'),
                                            DateUnit::MONTHS => __('advanced-tables::filter-builder.filters.date.unit.months.option'),
                                            DateUnit::QUARTERS => __('advanced-tables::filter-builder.filters.date.unit.quarters.option'),
                                            DateUnit::YEARS => __('advanced-tables::filter-builder.filters.date.unit.years.option'),
                                        ];
                                    }

                                    return [
                                        DateUnit::DAYS_AGO => __('advanced-tables::filter-builder.filters.date.unit.days_ago.option'),
                                        DateUnit::DAYS_FROM_NOW => __('advanced-tables::filter-builder.filters.date.unit.days_from_now.option'),
                                        DateUnit::WEEKS_AGO => __('advanced-tables::filter-builder.filters.date.unit.weeks_ago.option'),
                                        DateUnit::WEEKS_FROM_NOW => __('advanced-tables::filter-builder.filters.date.unit.weeks_from_now.option'),
                                        DateUnit::MONTHS_AGO => __('advanced-tables::filter-builder.filters.date.unit.months_ago.option'),
                                        DateUnit::MONTHS_FROM_NOW => __('advanced-tables::filter-builder.filters.date.unit.months_from_now.option'),
                                        DateUnit::QUARTERS_AGO => __('advanced-tables::filter-builder.filters.date.unit.quarters_ago.option'),
                                        DateUnit::QUARTERS_FROM_NOW => __('advanced-tables::filter-builder.filters.date.unit.quarters_from_now.option'),
                                        DateUnit::YEARS_AGO => __('advanced-tables::filter-builder.filters.date.unit.years_ago.option'),
                                        DateUnit::YEARS_FROM_NOW => __('advanced-tables::filter-builder.filters.date.unit.years_from_now.option'),
                                    ];
                                })
                                ->columnSpan([
                                    'default' => fn (Livewire $livewire, Get $get) => in_array($get('operator'), [DateOperator::IN_THIS, DateOperator::IS_NEXT, DateOperator::IS_LAST, DateOperator::BETWEEN]) ? 4 : 3,
                                    'sm' => function (Livewire $livewire, Get $get) {
                                        if ($this->hasWideLayout($livewire)) {
                                            return 6;
                                        }

                                        if (in_array($get('operator'), [DateOperator::IN_THIS, DateOperator::IS_NEXT, DateOperator::IS_LAST, DateOperator::BETWEEN])) {
                                            return 12;
                                        }

                                        return 9;
                                    },
                                ])
                                ->visible(fn (Get $get) => in_array($get('operator'), [DateOperator::IN_THIS, DateOperator::IS_NEXT, DateOperator::IS_LAST, DateOperator::IN_THE_NEXT, DateOperator::IN_THE_LAST, DateOperator::EXACTLY, DateOperator::BEFORE, DateOperator::AFTER, DateOperator::BETWEEN])),
                            DatePicker::make('date_start')
                                ->hiddenLabel()
                                ->columnSpan([
                                    'default' => 4,
                                    'sm' => fn (Livewire $livewire) => $this->hasWideLayout($livewire) ? 6 : 12,
                                ])
                                ->visible(fn (Get $get) => in_array($get('operator'), [DateOperator::IS_DATE, DateOperator::BEFORE_DATE, DateOperator::AFTER_DATE, DateOperator::BETWEEN_DATES])),
                            DatePicker::make('date_end')
                                ->hiddenLabel()
                                ->columnSpan([
                                    'default' => 4,
                                    'sm' => fn (Livewire $livewire) => $this->hasWideLayout($livewire) ? 6 : 12,
                                ])
                                ->visible(fn (Get $get) => in_array($get('operator'), [DateOperator::BETWEEN_DATES])),
                        ])
                        ->hidden(fn (Get $get) => blank($get('operator')) || in_array($get('operator'), [DateOperator::YESTERDAY, DateOperator::TODAY, DateOperator::TOMORROW]))
                        ->columnSpan([
                            'sm' => fn (Livewire $livewire) => $this->hasWideLayout($livewire) ? 3 : 4,
                        ])
                        ->extraAttributes(
                            fn (Livewire $livewire) => $this->hasWideLayout($livewire) ? ['class' => 'sm:mt-8'] : []
                        ),
                ]),
        ];
    }

    protected function applyQuery(Builder $query, array $data, string $column): Builder
    {
        if ($this->isRecentQuery($data)) {
            return $this->applyRecentQuery($query, $data, $column);
        }

        if ($this->isRelativeQuery($data)) {
            return $this->applyRelativeQuery($query, $data, $column);
        }

        return $this->applyAbsoluteQuery($query, $data, $column);
    }

    protected function formFilled(array $data): bool
    {
        $operator = $data['operator'] ?? null;

        if (blank($operator)) {
            return false;
        }

        $value = $data['value'] ?? null;
        $betweenStart = $data['between_start'] ?? null;
        $betweenEnd = $data['between_end'] ?? null;
        $unit = $data['unit'] ?? null;
        $dateStart = $data['date_start'] ?? null;
        $dateEnd = $data['date_end'] ?? null;

        if (in_array($operator, [DateOperator::IN_THIS, DateOperator::IS_NEXT, DateOperator::IS_LAST]) && blank($unit)) {
            return false;
        }

        if (in_array($operator, [DateOperator::IN_THE_NEXT, DateOperator::IN_THE_LAST, DateOperator::EXACTLY, DateOperator::BEFORE, DateOperator::AFTER]) && (blank($value) || blank($unit))) {
            return false;
        }

        if (in_array($operator, [DateOperator::BETWEEN]) && (blank($betweenStart) || blank($betweenEnd) || blank($unit))) {
            return false;
        }

        if (in_array($operator, [DateOperator::IS_DATE, DateOperator::BEFORE_DATE, DateOperator::AFTER_DATE]) && blank($dateStart)) {
            return false;
        }

        if (in_array($operator, [DateOperator::BETWEEN_DATES]) && (blank($dateStart) || blank($dateEnd))) {
            return false;
        }

        return true;
    }

    protected function getFilterIndicator(array $data): array
    {
        $operator = $data['operator'];

        $dateStart = $this->formatDate($this->column, $data['date_start']);
        $dateEnd = $this->formatDate($this->column, $data['date_end']);

        $columnLabel = strip_tags($this->column->getLabel());
        $operatorLabel = __('advanced-tables::filter-builder.filters.date.' . $operator . '.indicator');

        $unitLabel = $data['value'] == 1
            ? __('advanced-tables::filter-builder.filters.date.unit.' . $data['unit'] . '.indicator_singular')
            : __('advanced-tables::filter-builder.filters.date.unit.' . $data['unit'] . '.indicator');

        if (in_array($operator, [DateOperator::YESTERDAY, DateOperator::TODAY, DateOperator::TOMORROW])) {
            return ["{$columnLabel} {$operatorLabel}"];
        }

        if (in_array($operator, [DateOperator::IN_THIS, DateOperator::IS_NEXT, DateOperator::IS_LAST])) {
            return ["{$columnLabel} {$operatorLabel} {$unitLabel}"];
        }

        if (in_array($operator, [DateOperator::IN_THE_NEXT, DateOperator::IN_THE_LAST, DateOperator::EXACTLY, DateOperator::BEFORE, DateOperator::AFTER])) {
            return ["{$columnLabel} {$operatorLabel} {$data['value']} {$unitLabel}"];
        }

        if (in_array($operator, [DateOperator::IS_DATE, DateOperator::BEFORE_DATE, DateOperator::AFTER_DATE])) {
            return ["{$columnLabel} {$operatorLabel} {$dateStart}"];
        }

        if (in_array($operator, [DateOperator::BETWEEN])) {
            return ["{$columnLabel} {$operatorLabel} {$data['between_start']} " . __('advanced-tables::filter-builder.filters.operators.and') . " {$data['between_end']} {$unitLabel}"];
        }

        return ["{$columnLabel} {$operatorLabel} {$dateStart} " . __('advanced-tables::filter-builder.filters.operators.and') . " {$dateEnd}"];
    }

    protected function getOperators(): array
    {
        return [
            __('advanced-tables::filter-builder.form.recent') => [
                DateOperator::YESTERDAY => __('advanced-tables::filter-builder.filters.date.yesterday.option'),
                DateOperator::TODAY => __('advanced-tables::filter-builder.filters.date.today.option'),
                DateOperator::TOMORROW => __('advanced-tables::filter-builder.filters.date.tomorrow.option'),
            ],
            __('advanced-tables::filter-builder.form.relative') => [
                DateOperator::IN_THIS => __('advanced-tables::filter-builder.filters.date.in_this.option'),
                DateOperator::IS_NEXT => __('advanced-tables::filter-builder.filters.date.is_next.option'),
                DateOperator::IS_LAST => __('advanced-tables::filter-builder.filters.date.is_last.option'),
                DateOperator::IN_THE_NEXT => __('advanced-tables::filter-builder.filters.date.in_the_next.option'),
                DateOperator::IN_THE_LAST => __('advanced-tables::filter-builder.filters.date.in_the_last.option'),
                DateOperator::EXACTLY => __('advanced-tables::filter-builder.filters.date.exactly.option'),
                DateOperator::BEFORE => __('advanced-tables::filter-builder.filters.date.before.option'),
                DateOperator::AFTER => __('advanced-tables::filter-builder.filters.date.after.option'),
                DateOperator::BETWEEN => __('advanced-tables::filter-builder.filters.date.between.option'),
            ],
            __('advanced-tables::filter-builder.form.absolute') => [
                DateOperator::IS_DATE => __('advanced-tables::filter-builder.filters.date.is_date.option'),
                DateOperator::BEFORE_DATE => __('advanced-tables::filter-builder.filters.date.before_date.option'),
                DateOperator::AFTER_DATE => __('advanced-tables::filter-builder.filters.date.after_date.option'),
                DateOperator::BETWEEN_DATES => __('advanced-tables::filter-builder.filters.date.between_dates.option'),
            ],
        ];
    }

    protected function getOperatorOptions(): array
    {
        $operators = $this->getOperators();

        return collect($operators)
            ->map(
                fn ($operatorGroup) => collect($operatorGroup)
                    ->filter(
                        fn ($name, $operator) => $this->operatorIsIncluded($operator)
                    )
            )
            ->filter(
                fn ($operatorGroup) => collect($operatorGroup)
                    ->isNotEmpty()
            )
            ->toArray();
    }

    protected function formatDate(Column $column, $date)
    {
        return $column->evaluate(invade($column)->formatStateUsing, [
            'state' => $date,
        ]);
    }
}
