<?php

namespace Archilex\AdvancedTables\Filters\Concerns;

use Closure;

trait HasIcons
{
    protected array | Closure $icons = [];

    public function icons(array | Closure $icons): static
    {
        $this->icons = $icons;

        return $this;
    }

    public function getIcons(): array
    {
        return $this->evaluate($this->icons);
    }
}
