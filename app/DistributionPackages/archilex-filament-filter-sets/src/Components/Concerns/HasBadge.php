<?php

namespace Archilex\AdvancedTables\Components\Concerns;

use Closure;

trait HasBadge
{
    protected string | Closure | null $badge = null;

    protected string | Closure | null $badgeColor = null;

    public function badge(string | Closure | null $badge): static
    {
        $this->badge = $badge;

        return $this;
    }

    public function getBadge(): ?string
    {
        return $this->evaluate($this->badge);
    }

    public function badgeColor(string | Closure | null $badgeColor): static
    {
        $this->badgeColor = $badgeColor;

        return $this;
    }

    public function getBadgeColor(): ?string
    {
        return $this->evaluate($this->badgeColor);
    }
}
