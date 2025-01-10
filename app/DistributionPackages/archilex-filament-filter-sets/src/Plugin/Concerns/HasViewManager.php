<?php

namespace Archilex\AdvancedTables\Plugin\Concerns;

use Closure;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Support\Enums\IconPosition;

trait HasViewManager
{
    use EvaluatesClosures;

    protected bool|Closure $isViewManagerInFavoritesBar = true;

    protected bool|Closure $isViewManagerInTable = false;

    protected string|Closure $viewManagerPosition = 'end';

    protected string|Closure $viewManagerTablePosition = 'tables::toolbar.search.after';

    protected string|Closure $viewManagerIcon = 'heroicon-o-queue-list';

    protected string|IconPosition|Closure $viewManagerIconPosition = IconPosition::Before;

    protected bool|Closure $showViewManagerAsSlideOver = false;

    protected bool|Closure $showViewManagerAsButton = false;

    protected string|Closure $viewManagerButtonLabel = 'Views';

    protected string|Closure $viewManagerButtonSize = 'md';

    protected bool|Closure $showViewManagerButtonOutlined = false;

    protected bool|Closure $hasSaveInViewManager = false;

    protected bool|Closure $hasResetInViewManager = false;

    protected bool|Closure $hasSearchInViewManager = true;

    protected bool|Closure $hasViewManagerBadge = true;

    protected bool|Closure $canClickToApply = true;

    protected bool|Closure $hasApplyButtonInViewManager = true;

    protected bool|Closure $hasViewTypeBadges = false;

    protected bool|Closure $hasViewTypeIcons = true;

    protected bool|Closure $hasPublicIndicatorWhenGlobal = false;

    protected bool|Closure $hasActiveViewBadge = false;

    protected bool|Closure $hasActiveViewIndicator = true;

    protected bool|Closure $showViewIcon = true;

    protected string|Closure $defaultViewIcon = 'heroicon-o-funnel';

    public function viewManagerInFavoritesBar(bool|Closure $condition = true, string|Closure $position = 'end'): static
    {
        $this->isViewManagerInFavoritesBar = $condition;

        $this->viewManagerPosition = $position;

        return $this;
    }

    public function viewManagerInTable(bool|Closure $condition = true, string|Closure $position = 'tables::toolbar.search.after'): static
    {
        $this->isViewManagerInTable = $condition;

        $this->viewManagerTablePosition = $position;

        return $this;
    }

    public function viewManagerSlideOver(bool|Closure $condition = true): static
    {
        $this->showViewManagerAsSlideOver = $condition;

        return $this;
    }

    public function viewManagerButton(bool|Closure $condition = true, string|Closure $label = 'Views'): static
    {
        $this->showViewManagerAsButton = $condition;

        $this->viewManagerButtonLabel = $label;

        return $this;
    }

    public function viewManagerButtonSize(string|Closure $size = 'md'): static
    {
        $this->viewManagerButtonSize = $size;

        return $this;
    }

    public function viewManagerButtonOutlined(bool|Closure $condition = true): static
    {
        $this->showViewManagerButtonOutlined = $condition;

        return $this;
    }

    public function viewManagerSaveView(bool|Closure $condition = true): static
    {
        $this->hasSaveInViewManager = $condition;

        return $this;
    }

    public function viewManagerResetView(bool|Closure $condition = true): static
    {
        $this->hasResetInViewManager = $condition;

        return $this;
    }

    public function viewManagerSearch(bool|Closure $condition = true): static
    {
        $this->hasSearchInViewManager = $condition;

        return $this;
    }

    public function viewManagerIcon(string|Closure $icon): static
    {
        $this->viewManagerIcon = $icon;

        return $this;
    }

    public function viewManagerIconPosition(string|IconPosition|Closure $position = IconPosition::Before): static
    {
        $this->viewManagerIconPosition = $position;

        return $this;
    }

    public function viewManagerBadge(bool|Closure $condition = true): static
    {
        $this->hasViewManagerBadge = $condition;

        return $this;
    }

    public function viewManagerClickToApply(bool|Closure $condition = true): static
    {
        $this->canClickToApply = $condition;

        return $this;
    }

    public function viewManagerApplyButton(bool|Closure $condition = true): static
    {
        $this->hasApplyButtonInViewManager = $condition;

        return $this;
    }

    public function viewManagerViewTypeBadges(bool|Closure $condition = true): static
    {
        $this->hasViewTypeBadges = $condition;

        return $this;
    }

    public function viewManagerViewTypeIcons(bool|Closure $condition = true): static
    {
        $this->hasViewTypeIcons = $condition;

        return $this;
    }

    public function viewManagerPublicIndicatorWhenGlobal(bool|Closure $condition = true): static
    {
        $this->hasPublicIndicatorWhenGlobal = $condition;

        return $this;
    }

    public function viewManagerActiveViewBadge(bool|Closure $condition = true): static
    {
        $this->hasActiveViewBadge = $condition;

        return $this;
    }

    public function viewManagerActiveViewIndicator(bool|Closure $condition = true): static
    {
        $this->hasActiveViewIndicator = $condition;

        return $this;
    }

    public function viewIcon(bool|Closure $condition = true): static
    {
        $this->showViewIcon = $condition;

        return $this;
    }

    public function defaultViewIcon(string|Closure $icon): static
    {
        $this->defaultViewIcon = $icon;

        return $this;
    }

    public function isViewManagerInFavoritesBar(): bool
    {
        return $this->evaluate($this->isViewManagerInFavoritesBar);
    }

    public function isViewManagerInTable(): bool
    {
        return $this->evaluate($this->isViewManagerInTable);
    }

    public function viewManagerPosition(): string
    {
        return $this->evaluate($this->viewManagerPosition);
    }

    public function viewManagerTablePosition(): string
    {
        return $this->evaluate($this->viewManagerTablePosition);
    }

    public function showViewManagerAsSlideOver(): bool
    {
        return $this->evaluate($this->showViewManagerAsSlideOver);
    }

    public function showViewManagerAsButton(): bool
    {
        return $this->evaluate($this->showViewManagerAsButton);
    }

    public function getViewManagerButtonLabel(): string
    {
        return $this->evaluate($this->viewManagerButtonLabel);
    }

    public function getViewManagerButtonSize(): string
    {
        return $this->evaluate($this->viewManagerButtonSize);
    }

    public function showViewManagerButtonOutlined(): bool
    {
        return $this->evaluate($this->showViewManagerButtonOutlined);
    }

    public function hasSaveInViewManager(): bool
    {
        return $this->evaluate($this->hasSaveInViewManager);
    }

    public function hasResetInViewManager(): bool
    {
        return $this->evaluate($this->hasResetInViewManager);
    }

    public function hasSearchInViewManager(): bool
    {
        return $this->evaluate($this->hasSearchInViewManager);
    }

    public function hasViewManagerBadge(): bool
    {
        return $this->evaluate($this->hasViewManagerBadge);
    }

    public function canClickToApply(): bool
    {
        return $this->evaluate($this->canClickToApply);
    }

    public function getViewManagerIcon(): string
    {
        return $this->evaluate($this->viewManagerIcon);
    }

    public function getViewManagerIconPosition(): string|IconPosition|null
    {
        return $this->evaluate($this->viewManagerIconPosition);
    }

    public function hasApplyButtonInViewManager(): bool
    {
        return $this->evaluate($this->hasApplyButtonInViewManager);
    }

    public function hasViewTypeBadges(): bool
    {
        return $this->evaluate($this->hasViewTypeBadges);
    }

    public function hasViewTypeIcons(): bool
    {
        return $this->evaluate($this->hasViewTypeIcons);
    }

    public function hasPublicIndicatorWhenGlobal(): bool
    {
        return $this->evaluate($this->hasPublicIndicatorWhenGlobal);
    }

    public function hasActiveViewBadge(): bool
    {
        return $this->evaluate($this->hasActiveViewBadge);
    }

    public function hasActiveViewIndicator(): bool
    {
        return $this->evaluate($this->hasActiveViewIndicator);
    }

    public function showViewIcon(): bool
    {
        return $this->evaluate($this->showViewIcon);
    }

    public function getDefaultViewIcon(): string
    {
        return $this->evaluate($this->defaultViewIcon);
    }
}
