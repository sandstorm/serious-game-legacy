<?php

namespace Archilex\AdvancedTables\Filters\Concerns;

trait CanIncludeColumns
{
    protected array $excludedColumns = [];

    protected array $includedColumns = [];

    protected bool $mapColumns = false;

    public function includeColumns(bool | array $columns = true): static
    {
        $this->mapColumns = true;

        if (is_array($columns)) {
            $this->includedColumns = $columns;
        }

        return $this;
    }

    public function excludeColumns(array $columns): static
    {
        $this->excludedColumns = $columns;

        return $this;
    }

    protected function columnIsExcluded(string $column): bool
    {
        return in_array($column, $this->excludedColumns);
    }

    protected function columnIsIncluded(string $column): bool
    {
        if ($this->columnIsExcluded($column)) {
            return false;
        }

        if (empty($this->includedColumns)) {
            return true;
        }

        return in_array($column, $this->includedColumns);
    }
}
