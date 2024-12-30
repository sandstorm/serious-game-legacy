<?php

namespace Archilex\AdvancedTables\Filters\Concerns;

use Closure;

trait HasOrGroups
{
    protected bool | Closure $hasOrGroups = true;

    public function orGroups(bool | Closure $condition): static
    {
        $this->hasOrGroups = $condition;

        return $this;
    }

    public function hasOrGroups(): bool
    {
        return $this->evaluate($this->hasOrGroups);
    }
}
