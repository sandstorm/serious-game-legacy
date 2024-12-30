<?php

namespace Archilex\AdvancedTables\Components\Concerns;

use Closure;

trait CanBeDefault
{
    protected bool | Closure $isDefault = false;

    public function default(bool | Closure $condition = true): static
    {
        $this->isDefault = $condition;

        return $this;
    }

    public function isDefault(): bool
    {
        return (bool) $this->evaluate($this->isDefault);
    }
}
