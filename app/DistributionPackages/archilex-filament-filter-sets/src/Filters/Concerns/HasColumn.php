<?php

namespace Archilex\AdvancedTables\Filters\Concerns;

use Filament\Tables\Columns\Column;

trait HasColumn
{
    protected ?Column $column = null;

    public function column(Column $column): static
    {
        $this->column = $column;

        return $this;
    }

    public function getColumn(): ?Column
    {
        return $this->column;
    }
}
