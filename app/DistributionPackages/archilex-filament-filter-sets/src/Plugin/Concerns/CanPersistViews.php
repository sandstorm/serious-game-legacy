<?php

namespace Archilex\AdvancedTables\Plugin\Concerns;

use Closure;
use Filament\Support\Concerns\EvaluatesClosures;

trait CanPersistViews
{
    use EvaluatesClosures;

    protected bool | Closure $persistsActiveViewInSession = false;

    public function persistActiveViewInSession(bool | Closure $condition = true): static
    {
        $this->persistsActiveViewInSession = $condition;

        return $this;
    }

    public function persistsActiveViewInSession(): bool
    {
        return $this->evaluate($this->persistsActiveViewInSession);
    }
}
