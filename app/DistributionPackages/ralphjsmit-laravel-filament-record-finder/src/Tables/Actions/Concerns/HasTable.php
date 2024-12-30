<?php

namespace RalphJSmit\Filament\RecordFinder\Tables\Actions\Concerns;

use Closure;
use Filament\Tables\Grouping\Group;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/*
 * When changing anything relating to table configuration,
 * please ensure to keep the trait in sync with the form
 * component trait, so that the same settings are used.
 */
trait HasTable
{
    protected Builder | Relation | Closure | null $tableQuery = null;

    protected bool | Closure $tableStandalone = false;

    protected array | Closure $tableColumns = [];

    protected array | Closure $tableGroups = [];

    protected Group | null | string $tableDefaultGroup = null;

    protected bool | Closure $tableDeselectAllRecordsWhenFiltered = false;

    protected bool | Closure $tableGroupingSettingsHidden = false;

    protected array | Closure $tableFilters = [];

    protected array | Closure $tableHeaderActions = [];

    protected array | Closure $tableActions = [];

    protected array | Closure $tableBulkActions = [];

    protected array | Closure $tableEmptyStateActions = [];

    /**
     * @var array<int | string> | Closure | null
     */
    protected array | Closure | null $tablePaginationPageOptions = null;

    protected ?Closure $modifyTableCallback = null;

    public function tableQuery(Builder | Relation | Closure | null $query): static
    {
        $this->tableQuery = $query;

        return $this;
    }

    public function query(Builder | Relation | Closure | null $query): static
    {
        return $this->tableQuery($query);
    }

    public function tableStandalone(bool | Closure $condition = true): static
    {
        $this->tableStandalone = $condition;

        return $this;
    }

    public function standalone(bool | Closure $condition = true): static
    {
        return $this->tableStandalone($condition);
    }

    public function tableColumns(array | Closure $columns): static
    {
        $this->tableColumns = $columns;

        return $this;
    }

    public function tableGroups(array | Closure $groups): static
    {
        $this->tableGroups = $groups;

        return $this;
    }

    public function tableDefaultGroup(Group | null | string $group): static
    {
        $this->tableDefaultGroup = $group;

        return $this;
    }

    public function tableDeselectAllRecordsWhenFiltered(bool | Closure $condition = true): static
    {
        $this->tableDeselectAllRecordsWhenFiltered = $condition;

        return $this;
    }

    public function tableGroupingSettingsHidden(bool | Closure $hidden = true): static
    {
        $this->tableGroupingSettingsHidden = $hidden;

        return $this;
    }

    public function tableFilters(array | Closure $filters): static
    {
        $this->tableFilters = $filters;

        return $this;
    }

    public function tableHeaderActions(array | Closure $actions): static
    {
        $this->tableHeaderActions = $actions;

        return $this;
    }

    public function tableActions(array | Closure $actions): static
    {
        $this->tableActions = $actions;

        return $this;
    }

    public function tableBulkActions(array | Closure $actions): static
    {
        $this->tableBulkActions = $actions;

        return $this;
    }

    public function tableEmptyStateActions(array | Closure $actions): static
    {
        $this->tableEmptyStateActions = $actions;

        return $this;
    }

    /**
     * @param  array<int | string> | Closure | null  $options
     */
    public function tablePaginationPageOptions(array | Closure | null $options): static
    {
        $this->tablePaginationPageOptions = $options;

        return $this;
    }

    public function modifyTableUsing(?Closure $callback): static
    {
        $this->modifyTableCallback = $callback;

        return $this;
    }

    public function getTableQuery(): Builder | Relation
    {
        $tableQuery = $this->evaluate($this->tableQuery);

        if ($tableQuery instanceof Relation) {
            // Cannot serialize `Relation`, converting automatically to a builder...
            $tableQuery = $tableQuery->getQuery();
        }

        return $tableQuery;
    }

    public function isTableStandalone(): bool
    {
        return $this->evaluate($this->tableStandalone);
    }

    public function getTableColumns(): array
    {
        return $this->evaluate($this->tableColumns);
    }

    public function getTableGroups(): array
    {
        return $this->evaluate($this->tableGroups);
    }

    public function getTableDefaultGroup(): Group | null | string
    {
        return $this->evaluate($this->tableDefaultGroup);
    }

    public function shouldTableDeselectAllRecordsWhenFiltered(): bool
    {
        return $this->evaluate($this->tableDeselectAllRecordsWhenFiltered);
    }

    public function areTableGroupingSettingsHidden(): bool
    {
        return $this->evaluate($this->tableGroupingSettingsHidden);
    }

    public function getTableFilters(): array
    {
        return $this->evaluate($this->tableFilters);
    }

    public function getTableHeaderActions(): array
    {
        return $this->evaluate($this->tableHeaderActions);
    }

    public function getTableActions(): array
    {
        return $this->evaluate($this->tableActions);
    }

    public function getTableBulkActions(): array
    {
        return $this->evaluate($this->tableBulkActions);
    }

    public function getTableEmptyStateActions(): array
    {
        return $this->evaluate($this->tableEmptyStateActions);
    }

    /**
     * @return array<int | string>| null
     */
    public function getTablePaginationPageOptions(): ?array
    {
        return $this->evaluate($this->tablePaginationPageOptions);

        if ($tablePaginatedPageOptions = $this->evaluate($this->tablePaginationPageOptions)) {
            return $tablePaginatedPageOptions;
        }

        if (! $this->getOpenModalIsModalSlideOver()) {
            return [5, 10];
        }

        return [];
    }

    public function getModifyTableCallback(): ?Closure
    {
        return $this->modifyTableCallback;
    }
}
