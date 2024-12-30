<?php

namespace Archilex\AdvancedTables\Plugin\Concerns;

use Closure;
use Filament\Support\Concerns\EvaluatesClosures;

trait HasResource
{
    use EvaluatesClosures;

    protected bool | Closure $resourceIsEnabled = true;

    protected bool | Closure $hasResourceNavigationBadge = true;

    protected string | Closure | null $resourceNavigationIcon = 'heroicon-o-funnel';

    protected string | Closure | null $resourceNavigationGroup = null;

    protected int | Closure | null $resourceNavigationSort = null;

    protected bool | Closure $resourceLoadsAllUsers = true;

    protected array | Closure | null $resourcePanels = null;

    public function resourceEnabled(bool | Closure $condition = true): static
    {
        $this->resourceIsEnabled = $condition;

        return $this;
    }

    public function resourceNavigationBadge(bool | Closure $condition): static
    {
        $this->hasResourceNavigationBadge = $condition;

        return $this;
    }

    public function resourceNavigationIcon(string | Closure | null $icon = null): static
    {
        $this->resourceNavigationIcon = $icon;

        return $this;
    }

    public function resourceNavigationGroup(string | closure $group): static
    {
        $this->resourceNavigationGroup = $group;

        return $this;
    }

    public function resourceNavigationSort(int | closure $sort): static
    {
        $this->resourceNavigationSort = $sort;

        return $this;
    }

    public function resourceLoadAllUsers(bool | closure $condition = true): static
    {
        $this->resourceLoadsAllUsers = $condition;

        return $this;
    }

    public function resourcePanels(array | Closure $panels): static
    {
        $this->resourcePanels = $panels;

        return $this;
    }

    public function resourceIsEnabled(): bool
    {
        return $this->evaluate($this->resourceIsEnabled);
    }

    public function resourceLoadsAllUsers(): bool
    {
        return $this->evaluate($this->resourceLoadsAllUsers);
    }

    public function hasResourceNavigationBadge(): bool
    {
        return $this->evaluate($this->hasResourceNavigationBadge);
    }

    public function getResourceNavigationIcon(): ?string
    {
        return $this->evaluate($this->resourceNavigationIcon);
    }

    public function getResourceNavigationGroup(): ?string
    {
        return $this->evaluate($this->resourceNavigationGroup);
    }

    public function getResourceNavigationSort(): ?int
    {
        return $this->evaluate($this->resourceNavigationSort);
    }

    public function getResourcePanels(): ?array
    {
        return $this->evaluate($this->resourcePanels);
    }
}
