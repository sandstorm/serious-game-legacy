<?php

namespace Archilex\AdvancedTables\Plugin\Concerns;

use Archilex\AdvancedTables\Models\UserView;
use Closure;
use Filament\Support\Concerns\EvaluatesClosures;

trait HasUserViews
{
    use EvaluatesClosures;

    protected bool | Closure $canManageGlobalUserViews = true;

    protected bool | Closure $userViewsAreEnabled = true;

    protected string | Closure $newGlobalUserViewSortPosition = 'before';

    protected string | Closure $userView = UserView::class;

    public function globalUserViewsManageable(bool | Closure $condition = true): static
    {
        $this->canManageGlobalUserViews = $condition;

        return $this;
    }

    public function newGlobalUserViewSortPosition(string | Closure $position = 'before'): static
    {
        $this->newGlobalUserViewSortPosition = $position;

        return $this;
    }

    public function userView(string | Closure $userView): static
    {
        $this->userView = $userView;

        return $this;
    }

    public function userViewsEnabled(bool | Closure $condition = true): static
    {
        $this->userViewsAreEnabled = $condition;

        return $this;
    }

    public function canManageGlobalUserViews(): bool
    {
        return $this->evaluate($this->canManageGlobalUserViews);
    }

    public function getNewGlobalUserViewSortPosition(): string
    {
        return $this->evaluate($this->newGlobalUserViewSortPosition);
    }

    public function getUserView(): string
    {
        return $this->evaluate($this->userView);
    }

    public function userViewsAreEnabled(): bool
    {
        return $this->evaluate($this->userViewsAreEnabled);
    }
}
