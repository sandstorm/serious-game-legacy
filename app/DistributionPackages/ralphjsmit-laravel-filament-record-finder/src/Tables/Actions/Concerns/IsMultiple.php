<?php

namespace RalphJSmit\Filament\RecordFinder\Tables\Actions\Concerns;

use Closure;

trait IsMultiple
{
    protected bool|Closure $isMultiple = false;

    public function multiple(bool|Closure $condition = true): static
    {
        $this->isMultiple = $condition;

        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->evaluate($this->isMultiple);
    }
}
