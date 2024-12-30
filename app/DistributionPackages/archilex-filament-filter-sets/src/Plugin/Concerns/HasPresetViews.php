<?php

namespace Archilex\AdvancedTables\Plugin\Concerns;

use Archilex\AdvancedTables\Models\ManagedPresetView;
use Closure;
use Filament\Support\Concerns\EvaluatesClosures;

trait HasPresetViews
{
    use EvaluatesClosures;

    protected bool | Closure $canCreateUsingPresetView = true;

    protected bool | Closure $canManagePresetViews = true;

    protected string | Closure $newPresetViewSortPosition = 'before';

    protected string | Closure | null $presetViewLockIcon = null;

    protected string | Closure $managedPresetView = ManagedPresetView::class;

    protected bool | Closure $hasPresetViewLegacyDropdown = false;

    public function createUsingPresetView(bool | Closure $condition = true): static
    {
        $this->canCreateUsingPresetView = $condition;

        return $this;
    }

    public function newPresetViewSortPosition(string | Closure $position = 'before'): static
    {
        $this->newPresetViewSortPosition = $position;

        return $this;
    }

    public function managedPresetView(string | Closure $managedPresetView): static
    {
        $this->managedPresetView = $managedPresetView;

        return $this;
    }

    public function presetViewsManageable(bool | Closure $condition = true): static
    {
        $this->canManagePresetViews = $condition;

        return $this;
    }

    public function presetViewLockIcon(string | Closure | null $icon = 'heroicon-o-lock-closed'): static
    {
        $this->presetViewLockIcon = $icon;

        return $this;
    }

    public function presetViewLegacyDropdown(bool | Closure $condition = true): static
    {
        $this->hasPresetViewLegacyDropdown = $condition;

        return $this;
    }

    public function canCreateUsingPresetView(): bool
    {
        return $this->evaluate($this->canCreateUsingPresetView);
    }

    public function canManagePresetViews(): bool
    {
        return $this->evaluate($this->canManagePresetViews);
    }

    public function getNewPresetViewSortPosition(): string
    {
        return $this->evaluate($this->newPresetViewSortPosition);
    }

    public function getManagedPresetView(): string
    {
        return $this->evaluate($this->managedPresetView);
    }

    public function getPresetViewLockIcon(): ?string
    {
        return $this->evaluate($this->presetViewLockIcon);
    }

    public function hasPresetViewLegacyDropdown(): bool
    {
        return $this->evaluate($this->hasPresetViewLegacyDropdown);
    }
}
