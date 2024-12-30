<?php

namespace Archilex\AdvancedTables\Components\Concerns;

use Closure;

trait CanBeFavorited
{
    protected bool | Closure $isFavorite = false;

    public function favorite(bool | Closure $condition = true): static
    {
        $this->isFavorite = $condition;

        return $this;
    }

    public function isFavorite(): bool
    {
        return (bool) $this->evaluate($this->isFavorite);
    }
}
