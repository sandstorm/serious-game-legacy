<?php

namespace Archilex\AdvancedTables\Filters\Concerns;

use Closure;

trait HasFilterPicker
{
    protected int | array | Closure $filterPickerColumns = 1;

    protected string | Closure | null $filterPickerMaxHeight = null;

    protected string | Closure | null $filterPickerWidth = null;

    protected bool | Closure $filterPickerHasSearch = false;

    protected array | Closure | bool $filterPickerFilterSort = true;

    /**
     * @param  int | array<string, int | null> | Closure  $columns
     */
    public function filterPickerColumns(int | array | Closure $columns): static
    {
        $this->filterPickerColumns = $columns;

        return $this;
    }

    public function filterPickerWidth(string | Closure | null $width): static
    {
        $this->filterPickerWidth = $width;

        return $this;
    }

    public function filterPickerMaxHeight(string | Closure | null $height): static
    {
        $this->filterPickerMaxHeight = $height;

        return $this;
    }

    public function filterPickerSearch(bool | Closure $condition = true): static
    {
        $this->filterPickerHasSearch = $condition;

        return $this;
    }

    public function filterPickerFilterSort(array | bool | Closure $sort = true): static
    {
        $this->filterPickerFilterSort = $sort;

        return $this;
    }

    /**
     * @return int | array<string, int | null>
     */
    public function getFilterPickerColumns(): int | array
    {
        return $this->evaluate($this->filterPickerColumns);
    }

    public function getFilterPickerMaxHeight(): ?string
    {
        return $this->evaluate($this->filterPickerMaxHeight);
    }

    public function filterPickerHasSearch(): bool
    {
        return $this->evaluate($this->filterPickerHasSearch);
    }

    public function getFilterPickerWidth(): ?string
    {
        return $this->evaluate($this->filterPickerWidth);
    }

    public function getFilterPickerFilterSort(): mixed
    {
        return $this->evaluate($this->filterPickerFilterSort);
    }
}
