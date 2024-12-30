<?php

namespace Archilex\AdvancedTables\Components\Concerns;

use Closure;

trait HasIndicator
{
    protected string | Closure | null $indicator = null;

    public function indicator(string | Closure | null $indicator): static
    {
        $this->indicator = $indicator;

        return $this;
    }

    public function getIndicator(): ?string
    {
        return $this->evaluate($this->indicator);
    }
}
