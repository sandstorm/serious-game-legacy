<?php

namespace Archilex\AdvancedTables\Components\Concerns;

use Closure;

trait HasDefaults
{
    protected array | Closure $defaultColumns = [];

    protected array | Closure | null $defaultFilters = null;

    protected string | Closure | null $defaultGrouping = null;

    protected string | Closure | null $defaultGroupingDirection = null;

    protected string | Closure | null $defaultSortColumn = null;

    protected string | Closure | null $defaultSortDirection = null;

    public function defaultColumns(array | Closure $columns): static
    {
        $this->defaultColumns = $columns;

        return $this;
    }

    public function defaultGrouping(string | Closure $group, string $direction = 'asc'): static
    {
        $this->defaultGrouping = $group;
        $this->defaultGroupingDirection = strtolower($direction);

        return $this;
    }

    public function defaultFilters(array | Closure $filters): static
    {
        $this->defaultFilters = $filters;

        return $this;
    }

    public function defaultSort(string | Closure $column, string $direction = 'asc'): static
    {
        $this->defaultSortColumn = $column;
        $this->defaultSortDirection = strtolower($direction);

        return $this;
    }

    public function getDefaultColumns(): ?array
    {
        return $this->evaluate($this->defaultColumns);
    }

    public function getDefaultFilters(): ?array
    {
        return $this->evaluate($this->defaultFilters);
    }

    public function getDefaultGrouping(): ?string
    {
        return $this->evaluate($this->defaultGrouping);
    }

    public function getDefaultGroupingDirection(): ?string
    {
        return $this->evaluate($this->defaultGroupingDirection);
    }

    public function getDefaultSortColumn(): ?string
    {
        return $this->evaluate($this->defaultSortColumn);
    }

    public function getDefaultSortDirection(): ?string
    {
        return $this->evaluate($this->defaultSortDirection);
    }
}
