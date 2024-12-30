<?php

namespace Archilex\AdvancedTables\Filters;

use Archilex\AdvancedTables\Filters\Concerns\HasColumn;
use Archilex\AdvancedTables\Filters\Concerns\HasFiltersLayout;
use Archilex\AdvancedTables\Filters\Concerns\HasOperators;
use Archilex\AdvancedTables\Filters\Concerns\HasQueryColumn;
use Archilex\AdvancedTables\Filters\Concerns\HasSelect;
use Archilex\AdvancedTables\Filters\Operators\TextOperator;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Tables\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Livewire\Component as Livewire;

class TextFilter extends BaseFilter
{
    use HasColumn;
    use HasFiltersLayout;
    use HasOperators;
    use HasQueryColumn;
    use HasSelect;

    protected bool $asSelect = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->indicateUsing(function (TextFilter $filter, array $state): array {
            if (! $this->formFilled($state)) {
                return [];
            }

            return $this->getFilterIndicator($filter, $state);
        });
    }

    public function asSelect(bool $condition = true): static
    {
        $this->asSelect = $condition;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function apply(Builder $query, array $data = []): Builder
    {
        if (! $this->formFilled($data)) {
            return $query;
        }

        $value = $this->getValue($data);

        $queryColumn = $this->getQueryColumn($query);

        $columnQueriesRelationships = $this->column->hasRelationship($query->getModel());

        if (
            ! $columnQueriesRelationships &&
            in_array($data['operator'], [TextOperator::IS_EMPTY, TextOperator::IS_NOT_EMPTY])
        ) {
            return $query->where(
                fn ($query) => $query->where($queryColumn, $data['operator'] === TextOperator::IS_EMPTY ? '=' : '!=', '')
                    ->{
                    $data['operator'] === TextOperator::IS_EMPTY
                        ? 'orWhereNull'
                        : 'whereNotNull'
                    }($queryColumn)
            );
        }

        if (
            ! $columnQueriesRelationships &&
            $this->isMultiple() &&
            in_array($data['operator'], [TextOperator::IS, TextOperator::IS_NOT])
        ) {
            return $query->{
                $data['operator'] === TextOperator::IS
                    ? 'whereIn'
                    : 'whereNotIn'
                }(
                    $queryColumn,
                    $data['values']
                );
        }

        $operator = $this->getOperator($data);

        if (! $columnQueriesRelationships) {
            return $query->where(
                $queryColumn,
                $operator,
                $value
            );
        }

        if (in_array($data['operator'], [TextOperator::IS_EMPTY, TextOperator::IS_NOT_EMPTY])) {
            return $query->where(function (Builder $query) use ($data, $queryColumn) {
                return $query->{
                        $data['operator'] === TextOperator::IS_EMPTY
                            ? 'doesntHave'
                            : 'has'
                        }($this->column->getRelationshipName())
                            ->orWhereHas(
                                $this->column->getRelationshipName(),
                                function (Builder $query) use ($data, $queryColumn) {
                                    return $query
                                        ->where($queryColumn, $data['operator'] === TextOperator::IS_EMPTY ? '=' : '!=', '')
                                        ->{
                                            $data['operator'] === TextOperator::IS_EMPTY
                                                ? 'orWhereNull'
                                                : 'whereNotNull'
                                            }($queryColumn);
                                }
                            );
            });
        }

        if (in_array($data['operator'], [TextOperator::IS, TextOperator::IS_NOT])) {
            return $query->{
                $data['operator'] === TextOperator::IS
                    ? 'whereHas'
                    : 'whereDoesntHave'
                }(
                    $this->column->getRelationshipName(),
                    function (Builder $query) use ($data, $queryColumn) {
                        if ($this->hasRelationship || filled($this->getOptions())) {
                            return $query->whereKey(
                                $this->isMultiple() ? $data['values'] : $data['value']
                            );
                        }

                        return $query->where($queryColumn, $data['value']);
                    },
                );
        }

        return $query->whereRelation(
            $this->column->getRelationshipName(),
            $queryColumn,
            $operator,
            $value,
        );
    }

    public function getFormSchema(): array
    {
        $schema = [];

        if ($this->hasRelationship || filled($this->getOptions())) {
            $schema[] = $this->getSelectField();
        }

        $schema[] = TextInput::make('value')
            ->hiddenLabel()
            ->hidden(
                fn (Get $get) => blank($get('operator')) ||
                    in_array($get('operator'), [TextOperator::IS_EMPTY, TextOperator::IS_NOT_EMPTY]) ||
                    (
                        in_array($get('operator'), [TextOperator::IS, TextOperator::IS_NOT]) &&
                        ($this->hasRelationship || filled($this->getOptions()))
                    )
            )
            ->columnSpan([
                'sm' => fn (Livewire $livewire) => $this->hasWideLayout($livewire) ? 1 : 2,
            ]);

        return [
            Grid::make()
                ->columns([
                    'sm' => 4,
                    'lg' => 4,
                ])
                ->extraAttributes(function () {
                    return $this->asSelect
                        ? ['class' => 'advanced-tables-filter-block advanced-tables-filter-block-select-filter']
                        : ['class' => 'advanced-tables-filter-block advanced-tables-filter-block-text-filter'];
                })
                ->schema([
                    TextInput::make('column')
                        ->default($this->getName())
                        ->hidden(),
                    Select::make('operator')
                        ->label(strip_tags($this->getLabel()))
                        ->extraAttributes(function () {
                            return $this->asSelect
                                ? ['class' => 'advanced-tables-filter-operator advanced-tables-filter-operator-select-filter']
                                : ['class' => 'advanced-tables-filter-operator advanced-tables-filter-operator-text-filter'];
                        })
                        ->live()
                        ->default($this->getDefaultOperator())
                        ->options($this->getOperatorOptions())
                        ->columnSpan([
                            'sm' => fn (Livewire $livewire) => $this->hasWideLayout($livewire) ? 1 : 4,
                        ]),
                    Grid::make()
                        ->columns(['sm' => 2])
                        ->schema($schema)
                        ->extraAttributes(
                            fn (Livewire $livewire) => $this->hasWideLayout($livewire) ? ['class' => 'sm:mt-8'] : []
                        )
                        ->columnSpan([
                            'sm' => fn (Livewire $livewire) => $this->hasWideLayout($livewire) ? 3 : 4,
                        ]),
                ]),
        ];
    }

    protected function getOperators(): array
    {
        if ($this->asSelect) {
            return [
                TextOperator::IS => __('advanced-tables::filter-builder.filters.text.is.option'),
                TextOperator::IS_NOT => __('advanced-tables::filter-builder.filters.text.is_not.option'),
                TextOperator::IS_EMPTY => __('advanced-tables::filter-builder.filters.text.is_empty.option'),
                TextOperator::IS_NOT_EMPTY => __('advanced-tables::filter-builder.filters.text.is_not_empty.option'),
            ];
        }

        return [
            TextOperator::IS => __('advanced-tables::filter-builder.filters.text.is.option'),
            TextOperator::IS_NOT => __('advanced-tables::filter-builder.filters.text.is_not.option'),
            TextOperator::STARTS_WITH => __('advanced-tables::filter-builder.filters.text.starts_with.option'),
            TextOperator::DOES_NOT_START_WITH => __('advanced-tables::filter-builder.filters.text.does_not_start_with.option'),
            TextOperator::ENDS_WITH => __('advanced-tables::filter-builder.filters.text.ends_with.option'),
            TextOperator::DOES_NOT_END_WITH => __('advanced-tables::filter-builder.filters.text.does_not_end_with.option'),
            TextOperator::CONTAINS => __('advanced-tables::filter-builder.filters.text.contains.option'),
            TextOperator::DOES_NOT_CONTAIN => __('advanced-tables::filter-builder.filters.text.does_not_contain.option'),
            TextOperator::IS_EMPTY => __('advanced-tables::filter-builder.filters.text.is_empty.option'),
            TextOperator::IS_NOT_EMPTY => __('advanced-tables::filter-builder.filters.text.is_not_empty.option'),
        ];
    }

    protected function getOperator(array $data): string
    {
        return match ($data['operator']) {
            TextOperator::STARTS_WITH, TextOperator::ENDS_WITH, TextOperator::CONTAINS, TextOperator::IS => 'like',
            default => 'not like',
        };
    }

    protected function getValue(array $data): ?string
    {
        return match ($data['operator']) {
            TextOperator::STARTS_WITH, TextOperator::DOES_NOT_START_WITH => "{$data['value']}%",
            TextOperator::ENDS_WITH, TextOperator::DOES_NOT_END_WITH => "%{$data['value']}",
            TextOperator::CONTAINS, TextOperator::DOES_NOT_CONTAIN => "%{$data['value']}%",
            default => $data['value'],
        };
    }

    protected function getFilterIndicator(TextFilter $filter, array $data): array
    {
        $operator = $data['operator'];
        $column = $this->getTable()->getColumns()[$filter->getName()];
        $columnLabel = strip_tags($column->getLabel());
        $operatorLabel = __('advanced-tables::filter-builder.filters.text.' . $operator . '.indicator');

        if (in_array($operator, [TextOperator::IS_EMPTY, TextOperator::IS_NOT_EMPTY])) {

            return ["{$columnLabel} {$operatorLabel}"];
        }

        $label = $this->getIndicatorLabel($filter, $data);

        if (! $label) {
            return [];
        }

        return ["{$columnLabel} {$operatorLabel} {$label}"];
    }

    protected function formFilled(array $data): bool
    {
        if (blank($data['operator'] ?? null)) {
            return false;
        }

        if (
            ! in_array($data['operator'], [TextOperator::IS, TextOperator::IS_NOT, TextOperator::IS_EMPTY, TextOperator::IS_NOT_EMPTY]) &&
            blank($data['value'] ?? null)
        ) {
            return false;
        }

        if (
            in_array($data['operator'], [TextOperator::IS, TextOperator::IS_NOT]) &&
            ! $this->isMultiple() &&
            blank($data['value'] ?? null)
        ) {
            return false;
        }

        if (
            in_array($data['operator'], [TextOperator::IS, TextOperator::IS_NOT]) &&
            $this->isMultiple() &&
            blank($data['values'] ?? null)
        ) {
            return false;
        }

        return true;
    }

    protected function getIndicatorLabel(TextFilter $filter, array $state): ?string
    {
        if (
            in_array($state['operator'], [TextOperator::IS, TextOperator::IS_NOT]) &&
            $filter->isMultiple()
        ) {
            if ($filter->queriesRelationships()) {
                $relationshipQuery = $filter->getRelationshipQuery();

                $labels = $relationshipQuery
                    ->when(
                        $filter->getRelationship() instanceof \Znck\Eloquent\Relations\BelongsToThrough,
                        fn (Builder $query) => $query->distinct(),
                    )
                    ->whereKey($state['values'])
                    ->pluck($relationshipQuery->qualifyColumn($filter->getRelationshipTitleAttribute()))
                    ->all();
            } else {
                $labels = Arr::only($filter->getOptions(), $state['values']);
            }

            if (! count($labels)) {
                return null;
            }

            return collect($labels)
                ->map(fn ($label) => '"' . $label . '"')
                ->join(', ', ' ' . __('advanced-tables::filter-builder.filters.operators.or') . ' ');
        }

        if (
            in_array($state['operator'], [TextOperator::IS, TextOperator::IS_NOT]) &&
            $this->hasRelationship &&
            $filter->queriesRelationships()
        ) {
            return $filter->getRelationshipQuery()
                ->whereKey($state['value'])
                ->first()
                ?->getAttributeValue($filter->getRelationshipTitleAttribute());
        }

        return '"' . $state['value'] . '"';
    }
}
