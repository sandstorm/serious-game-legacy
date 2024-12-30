<?php

namespace Archilex\AdvancedTables\Plugin\Concerns;

use Archilex\AdvancedTables\Models\ManagedUserView;
use Closure;
use Filament\Support\Concerns\EvaluatesClosures;

trait HasManagedUserViews
{
    use EvaluatesClosures;

    protected string | Closure $managedUserView = ManagedUserView::class;

    public function managedUserView(string | Closure $managedUserView): static
    {
        $this->managedUserView = $managedUserView;

        return $this;
    }

    public function getManagedUserView(): string
    {
        return $this->evaluate($this->managedUserView);
    }
}
