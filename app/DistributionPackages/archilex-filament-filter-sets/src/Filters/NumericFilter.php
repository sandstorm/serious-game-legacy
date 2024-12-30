<?php

namespace Archilex\AdvancedTables\Filters;

use Archilex\AdvancedTables\Filters\Concerns\HasColumn;
use Archilex\AdvancedTables\Filters\Concerns\HasFiltersLayout;
use Archilex\AdvancedTables\Filters\Concerns\HasOperators;
use Archilex\AdvancedTables\Filters\Concerns\HasQueryColumn;
use Archilex\AdvancedTables\Filters\Operators\AggregateOperator;
use Archilex\AdvancedTables\Filters\Operators\NumericOperator;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Tables\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Livewire\Component as Livewire;

class NumericFilter extends BaseFilter
{
    use HasColumn;
    use HasFiltersLayout;
    use HasOperators;
    use HasQueryColumn;

    protected function setUp(): void
    {
        parent::setUp();

        $this->modifyBaseQueryUsing(function (Builder $query, ?string $index, array $data) {
            if (! $index) {
                return;
            }

            $query->when(
                (filled($this->column->getRelationshipsToCount())) ||
                (filled($this->column->getRelationshipToAvg()) && filled($this->column->getColumnToAvg())) ||
                (filled($this->column->getRelationshipToMax()) && filled($this->column->getColumnToMax())) ||
                (filled($this->column->getRelationshipToMin()) && filled($this->column->getColumnToMin())) ||
                (filled($this->column->getRelationshipToSum()) && filled($this->column->getColumnToSum())),
                fn (Builder $query) => $this->getAggregateRelationshipQuery($query, $index)
            );
        });

        $this->indicateUsing(function (NumericFilter $filter, array $state): array {
            if (! $this->formFilled($state)) {
                return [];
            }

            return $this->getFilterIndicator($state);
        });
    }

    protected function getAggregateColumnAlias(string $index): string
    {
        return strtolower($this->column->getName() . '_' . $index);
    }

    protected function getAggregateRelatedTableAlias(string $relatedTable, string $index): string
    {
        return strtolower('table_alias_' . $relatedTable . '_' . $index);
    }

    protected function getAggregateRelationshipQuery(Builder $query, string $index): Builder
    {
        $aggregateOperator = $this->getAggregateOperator();
        $relationship = $this->getAggregateRelationship($aggregateOperator);
        $aggregateColumn = $this->getAggregateRelationshipColumn($aggregateOperator);
        $columnAlias = $this->getAggregateColumnAlias($index);

        $relatedModel = is_array($relationship)
            ? $query->getRelation(array_key_first($relationship))
            : $query->getRelation($relationship);

        if (is_array($relationship)) {
            $this->evaluate($relationship[array_key_first($relationship)], ['query' => $relatedModel->getQuery()]);
        }

        $relatedTable = $relatedModel->getRelated()->getTable();
        $relatedTableAlias = $this->getAggregateRelatedTableAlias($relatedTable, $index);

        $joinQuery = $this->buildJoinQuery($relatedModel, $relatedTable, $aggregateColumn, $aggregateOperator, $columnAlias);

        $localKey = $relatedModel instanceof BelongsToMany
            ? $relatedModel->getRelatedKeyName()
            : $relatedModel->getLocalKeyName();

        $relatedTableForeignKey = $relatedModel instanceof BelongsToMany
            ? $relatedModel->getForeignPivotKeyName()
            : $relatedModel->getForeignKeyName();

        return $query->leftJoinSub(
            $joinQuery,
            $relatedTableAlias,
            fn (JoinClause $join) => $join->on($query->getModel()->getTable() . '.' . $localKey, '=', $relatedTableAlias . '.' . $relatedTableForeignKey)
        );
    }

    protected function buildJoinQuery(Relation $relatedModel, string $relatedTable, string $aggregateColumn, string $aggregateOperator, string $columnAlias)
    {
        if ($relatedModel instanceof BelongsToMany) {
            return $this->buildBelongsToManyJoinQuery($relatedModel, $relatedTable, $aggregateColumn, $aggregateOperator, $columnAlias);
        }

        return $this->buildStandardJoinQuery($relatedModel, $aggregateColumn, $aggregateOperator, $columnAlias);
    }

    protected function buildBelongsToManyJoinQuery(Relation $relatedModel, string $relatedTable, string $aggregateColumn, string $aggregateOperator, string $columnAlias)
    {
        $pivotTable = $relatedModel->getTable();
        $relatedTableForeignKey = $relatedModel->getForeignPivotKeyName();
        $relatedPivotKey = $relatedModel->getRelatedPivotKeyName();
        $parentKey = $relatedModel->getParentKeyName();

        return $relatedModel
            ->newPivotStatement()
            ->select($relatedTableForeignKey, DB::raw($aggregateOperator . '(' . $aggregateColumn . ') as ' . $columnAlias))
            ->join($relatedTable, $relatedTable . '.' . $parentKey, '=', $pivotTable . '.' . $relatedPivotKey)
            ->groupBy($relatedTableForeignKey);
    }

    protected function buildStandardJoinQuery(Relation $relatedModel, string $aggregateColumn, string $aggregateOperator, string $columnAlias)
    {
        $relatedTableForeignKey = $relatedModel->getForeignKeyName();

        return $relatedModel
            ->newQuery()
            ->select($relatedTableForeignKey, DB::raw($aggregateOperator . '(' . $aggregateColumn . ') as ' . $columnAlias))
            ->groupBy($relatedTableForeignKey);
    }

    protected function getAggregateOperator(): string
    {
        return match (true) {
            filled($this->column->getRelationshipsToCount()) => AggregateOperator::COUNT,
            filled($this->column->getRelationshipToAvg()) && filled($this->column->getColumnToAvg()) => AggregateOperator::AVG,
            filled($this->column->getRelationshipToMax()) && filled($this->column->getColumnToMax()) => AggregateOperator::MAX,
            filled($this->column->getRelationshipToMin()) && filled($this->column->getColumnToMin()) => AggregateOperator::MIN,
            filled($this->column->getRelationshipToSum()) && filled($this->column->getColumnToSum()) => AggregateOperator::SUM,
        };
    }

    protected function getAggregateRelationship(string | array $aggregateOperator): mixed
    {
        return match ($aggregateOperator) {
            AggregateOperator::COUNT => $this->column->getRelationshipsToCount(),
            AggregateOperator::AVG => $this->column->getRelationshipToAvg(),
            AggregateOperator::MAX => $this->column->getRelationshipToMax(),
            AggregateOperator::MIN => $this->column->getRelationshipToMin(),
            AggregateOperator::SUM => $this->column->getRelationshipToSum(),
        };
    }

    protected function getAggregateRelationshipColumn(string $aggregateOperator): string
    {
        return match ($aggregateOperator) {
            AggregateOperator::COUNT => '*',
            AggregateOperator::AVG => $this->column->getColumnToAvg(),
            AggregateOperator::MAX => $this->column->getColumnToMax(),
            AggregateOperator::MIN => $this->column->getColumnToMin(),
            AggregateOperator::SUM => $this->column->getColumnToSum(),
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function apply(Builder $query, array $data = []): Builder
    {
        if (! $this->formFilled($data)) {
            return $query;
        }

        $method = $this->getMethod($data);

        $operator = $this->getOperator($data);

        $value = $this->getValue($data);

        $column = $this->getQueryColumn($query);

        if (! $this->column->hasRelationship($query->getModel())) {
            return $query->when(
                $method === 'where',
                fn (Builder $query) => $query->{$method}($column, $operator, $value),
                fn (Builder $query) => $query->{$method}($column, [$value, $data['end']])
            );
        }

        return $query->whereHas(
            $this->column->getRelationshipName(),
            fn (Builder $query) => $query->when(
                $method === 'where',
                fn (Builder $query) => $query->{$method}($column, $operator, $value),
                fn (Builder $query) => $query->{$method}($column, [$value, $data['end']])
            )
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function applyAggregateToQuery(Builder $query, string $index, array $data = []): Builder
    {
        if (! $this->formFilled($data)) {
            return $query;
        }

        $aggregateOperator = $this->getAggregateOperator();
        $relationship = $this->getAggregateRelationship($aggregateOperator);
        $relatedModel = is_array($relationship)
            ? $query->getRelation(array_key_first($relationship))
            : $query->getRelation($relationship);
        $relatedTable = $relatedModel->getRelated()->getTable();
        $relatedTableAlias = $this->getAggregateRelatedTableAlias($relatedTable, $index);

        $method = $this->getMethod($data);
        $operator = $this->getOperator($data);
        $value = $this->getValue($data);

        $qualifiedColumn = $relatedTableAlias . '.' . $this->getAggregateColumnAlias($index);

        if (
            $this->aggregatesRelationship() &&
            $data['operator'] === NumericOperator::EQUAL_TO &&
            $data['value'] == 0
        ) {
            return $query->whereNull($qualifiedColumn);
        }

        if (
            $this->aggregatesRelationship() &&
            $data['operator'] === NumericOperator::NOT_EQUAL_TO &&
            $data['value'] == 0
        ) {
            return $query->whereNotNull($qualifiedColumn);
        }

        if (
            $this->aggregatesRelationship() &&
            $data['operator'] === NumericOperator::NOT_EQUAL_TO &&
            $data['value'] != 0
        ) {
            return $query->where(function (Builder $query) use ($qualifiedColumn, $operator, $value) {
                $query->where($qualifiedColumn, $operator, $value)->orWhereNull($qualifiedColumn);
            });
        }

        if (
            $this->aggregatesRelationship() &&
            $data['operator'] === NumericOperator::GREATER_THAN_OR_EQUAL_TO &&
            $data['value'] == 0
        ) {
            return $query->where(function (Builder $query) use ($qualifiedColumn, $operator, $value) {
                $query->where($qualifiedColumn, $operator, $value)->orWhereNull($qualifiedColumn);
            });
        }

        if (
            $this->aggregatesRelationship() &&
            in_array($data['operator'], [NumericOperator::LESS_THAN, NumericOperator::LESS_THAN_OR_EQUAL_TO])
        ) {
            return $query->where(function (Builder $query) use ($qualifiedColumn, $operator, $value) {
                $query->where($qualifiedColumn, $operator, $value)->orWhereNull($qualifiedColumn);
            });
        }

        return $query->when(
            $method === 'where',
            fn (Builder $query) => $query->{$method}($qualifiedColumn, $operator, $value),
            fn (Builder $query) => $query->{$method}($qualifiedColumn, [$value, $data['end']])
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function applyAggregateToBaseQuery(Builder $query, string $index, array $data = []): Builder
    {
        if ($this->isHidden()) {
            return $query;
        }

        if (! $this->hasBaseQueryModificationCallback()) {
            return $query;
        }

        if (! ($data['isActive'] ?? true)) {
            return $query;
        }

        $this->evaluate($this->modifyBaseQueryUsing, [
            'data' => $data,
            'query' => $query,
            'state' => $data,
            'index' => $index,
        ]);

        return $query;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function applyToBaseQuery(Builder $query, array $data = []): Builder
    {
        if ($this->isHidden()) {
            return $query;
        }

        if (! $this->hasBaseQueryModificationCallback()) {
            return $query;
        }

        if (! ($data['isActive'] ?? true)) {
            return $query;
        }

        $this->evaluate($this->modifyBaseQueryUsing, [
            'data' => $data,
            'query' => $query,
            'state' => $data,
            'index' => null,
        ]);

        return $query;
    }

    public function getFormSchema(): array
    {
        return [
            Grid::make()
                ->columns(['sm' => 4, 'lg' => 4])
                ->extraAttributes(['class' => 'advanced-tables-filter-block advanced-tables-filter-block-numeric-filter'])
                ->schema([
                    TextInput::make('column')
                        ->default($this->getName())
                        ->hidden(),
                    Select::make('operator')
                        ->label(strip_tags($this->getLabel()))
                        ->extraAttributes(['class' => 'advanced-tables-filter-operator advanced-tables-filter-operator-numeric-filter'])
                        ->live()
                        ->default($this->getDefaultOperator())
                        ->options($this->getOperatorOptions())
                        ->columnSpan([
                            'sm' => fn (Livewire $livewire) => $this->hasWideLayout($livewire) ? 1 : 4,
                        ]),
                    Grid::make()
                        ->columns(['default' => 2])
                        ->schema([
                            TextInput::make('value')
                                ->hiddenLabel()
                                ->numeric()
                                ->columnSpan([
                                    'default' => fn (Get $get) => in_array($get('operator'), [NumericOperator::BETWEEN, NumericOperator::NOT_BETWEEN]) ? 1 : 2,
                                    'sm' => function (Get $get, Livewire $livewire) {
                                        if ($this->hasWideLayout($livewire)) {
                                            return 1;
                                        }

                                        if (in_array($get('operator'), [NumericOperator::BETWEEN, NumericOperator::NOT_BETWEEN])) {
                                            return 1;
                                        }

                                        return 2;
                                    },
                                ]),
                            TextInput::make('end')
                                ->hiddenLabel()
                                ->numeric()
                                ->visible(fn (Get $get) => in_array($get('operator'), [NumericOperator::BETWEEN, NumericOperator::NOT_BETWEEN])),
                        ])
                        ->hidden(fn (Get $get) => blank($get('operator')) || in_array($get('operator'), [NumericOperator::POSITIVE, NumericOperator::NEGATIVE]))
                        ->extraAttributes(
                            fn (Livewire $livewire) => $this->hasWideLayout($livewire) ? ['class' => 'sm:mt-8'] : []
                        )
                        ->columnSpan([
                            'sm' => fn (Livewire $livewire) => $this->hasWideLayout($livewire) ? 3 : 4,
                        ]),
                ]),
        ];
    }

    protected function getMethod(array $data): string
    {
        return match ($data['operator']) {
            NumericOperator::BETWEEN => 'whereBetween',
            NumericOperator::NOT_BETWEEN => 'whereNotBetween',
            default => 'where',
        };
    }

    protected function getOperator(array $data): string
    {
        return match ($data['operator']) {
            NumericOperator::GREATER_THAN, NumericOperator::POSITIVE => '>',
            NumericOperator::GREATER_THAN_OR_EQUAL_TO => '>=',
            NumericOperator::LESS_THAN, NumericOperator::NEGATIVE => '<',
            NumericOperator::LESS_THAN_OR_EQUAL_TO => '<=',
            NumericOperator::NOT_EQUAL_TO => '!=',
            default => '=',
        };
    }

    protected function getValue(array $data): string
    {
        return match ($data['operator']) {
            NumericOperator::POSITIVE, NumericOperator::NEGATIVE => 0,
            default => $data['value']
        };
    }

    protected function getFilterIndicator(array $data): array
    {
        $operator = $data['operator'];

        $columnLabel = strip_tags($this->column->getLabel());
        $operatorLabel = __('advanced-tables::filter-builder.filters.numeric.' . $operator . '.indicator');

        if (in_array($operator, [NumericOperator::POSITIVE, NumericOperator::NEGATIVE])) {
            return ["{$columnLabel} {$operatorLabel}"];
        }

        if (! in_array($operator, [NumericOperator::BETWEEN, NumericOperator::NOT_BETWEEN])) {
            return ["{$columnLabel} {$operatorLabel} {$data['value']}"];
        }

        if (blank($data['end'] ?? null)) {
            return [];
        }

        return ["{$columnLabel} {$operatorLabel} {$data['value']} " . __('advanced-tables::filter-builder.filters.operators.and') . " {$data['end']}"];
    }

    protected function getOperators(): array
    {
        return [
            NumericOperator::EQUAL_TO => __('advanced-tables::filter-builder.filters.numeric.equal_to.option'),
            NumericOperator::NOT_EQUAL_TO => __('advanced-tables::filter-builder.filters.numeric.not_equal_to.option'),
            NumericOperator::GREATER_THAN => __('advanced-tables::filter-builder.filters.numeric.greater_than.option'),
            NumericOperator::GREATER_THAN_OR_EQUAL_TO => __('advanced-tables::filter-builder.filters.numeric.greater_than_or_equal_to.option'),
            NumericOperator::LESS_THAN => __('advanced-tables::filter-builder.filters.numeric.less_than.option'),
            NumericOperator::LESS_THAN_OR_EQUAL_TO => __('advanced-tables::filter-builder.filters.numeric.less_than_or_equal_to.option'),
            NumericOperator::BETWEEN => __('advanced-tables::filter-builder.filters.numeric.between.option'),
            NumericOperator::NOT_BETWEEN => __('advanced-tables::filter-builder.filters.numeric.not_between.option'),
            NumericOperator::POSITIVE => __('advanced-tables::filter-builder.filters.numeric.positive.option'),
            NumericOperator::NEGATIVE => __('advanced-tables::filter-builder.filters.numeric.negative.option'),
        ];
    }

    protected function formFilled(array $data): bool
    {
        if (blank($data['operator'] ?? null)) {
            return false;
        }

        if (! in_array($data['operator'], [NumericOperator::POSITIVE, NumericOperator::NEGATIVE]) && blank($data['value'] ?? null)) {
            return false;
        }

        if (in_array($data['operator'], [NumericOperator::BETWEEN, NumericOperator::NOT_BETWEEN]) && (blank($data['value'] ?? null) || blank($data['end'] ?? null))) {
            return false;
        }

        return true;
    }

    protected function aggregatesRelationship(): bool
    {
        return
            filled($this->column->getRelationshipsToCount()) ||
            (filled($this->column->getRelationshipToAvg()) && filled($this->column->getColumnToAvg())) ||
            (filled($this->column->getRelationshipToMax()) && filled($this->column->getColumnToMax())) ||
            (filled($this->column->getRelationshipToMin()) && filled($this->column->getColumnToMin())) ||
            (filled($this->column->getRelationshipToSum()) && filled($this->column->getColumnToSum()));
    }
}
