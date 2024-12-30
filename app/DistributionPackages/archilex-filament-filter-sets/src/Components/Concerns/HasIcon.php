<?php

namespace Archilex\AdvancedTables\Components\Concerns;

use Closure;

trait HasIcon
{
    protected string | Closure | null $icon = null;

    protected string | Closure | null $iconColor = null;

    protected string | Closure | null $iconPosition = null;

    public function icon(string | Closure | null $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function iconPosition(string | Closure | null $position): static
    {
        $this->iconPosition = $position;

        return $this;
    }

    public function iconColor(string | Closure | null $color): static
    {
        $this->iconColor = $color;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->evaluate($this->icon);
    }

    public function getIconColor(): ?string
    {
        return $this->evaluate($this->iconColor);
    }

    public function getIconPosition(): ?string
    {
        return $this->evaluate($this->iconPosition) ?? 'before';
    }
}
