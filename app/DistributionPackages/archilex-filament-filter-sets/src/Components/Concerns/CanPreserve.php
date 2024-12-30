<?php

namespace Archilex\AdvancedTables\Components\Concerns;

use Closure;

trait CanPreserve
{
    protected bool | Closure $shouldPreserveFilters = false;

    protected bool | Closure $shouldPreserveGrouping = false;

    protected bool | Closure $shouldPreserveGroupingDirection = false;

    protected bool | Closure $shouldPreserveSortColumn = false;

    protected bool | Closure $shouldPreserveSortDirection = false;

    protected bool | Closure $shouldPreserveColumns = false;

    public function preserveAll(bool | Closure $condition = true): static
    {
        $this->shouldPreserveFilters = $condition;
        $this->shouldPreserveGrouping = $condition;
        $this->shouldPreserveGroupingDirection = $condition;
        $this->shouldPreserveSortColumn = $condition;
        $this->shouldPreserveSortDirection = $condition;
        $this->shouldPreserveColumns = $condition;

        return $this;
    }

    public function preserveFilters(bool | Closure $condition = true): static
    {
        $this->shouldPreserveFilters = $condition;

        return $this;
    }

    public function preserveGrouping(bool | Closure $condition = true): static
    {
        $this->shouldPreserveGrouping = $condition;

        return $this;
    }

    public function preserveGroupingDirection(bool | Closure $condition = true): static
    {
        $this->shouldPreserveGroupingDirection = $condition;

        return $this;
    }

    public function preserveSortColumn(bool | Closure $condition = true): static
    {
        $this->shouldPreserveSortColumn = $condition;

        return $this;
    }

    public function preserveSortDirection(bool | Closure $condition = true): static
    {
        $this->shouldPreserveSortDirection = $condition;

        return $this;
    }

    public function preserveToggledColumns(bool | Closure $condition = true): static
    {
        $this->shouldPreserveColumns = $condition;

        return $this;
    }

    public function shouldPreserveFilters(): bool
    {
        return $this->evaluate($this->shouldPreserveFilters);
    }

    public function shouldPreserveGrouping(): bool
    {
        return $this->evaluate($this->shouldPreserveGrouping);
    }

    public function shouldPreserveGroupingDirection(): bool
    {
        return $this->evaluate($this->shouldPreserveGroupingDirection);
    }

    public function shouldPreserveSortColumn(): bool
    {
        return $this->evaluate($this->shouldPreserveSortColumn);
    }

    public function shouldPreserveSortDirection(): bool
    {
        return $this->evaluate($this->shouldPreserveSortDirection);
    }

    public function shouldPreserveColumns(): bool
    {
        return $this->evaluate($this->shouldPreserveColumns);
    }
}
