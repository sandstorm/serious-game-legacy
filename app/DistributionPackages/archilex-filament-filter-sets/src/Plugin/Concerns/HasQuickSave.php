<?php

namespace Archilex\AdvancedTables\Plugin\Concerns;

use Closure;
use Filament\Support\Concerns\EvaluatesClosures;

trait HasQuickSave
{
    use EvaluatesClosures;

    protected bool | Closure $isQuickSaveInFavoritesBar = true;

    protected bool | Closure $isQuickSaveInTable = false;

    protected string | Closure $quickSavePosition = 'end';

    protected string | Closure $quickSaveTablePosition = 'tables::toolbar.search.after';

    protected bool | Closure $showQuickSaveAsSlideOver = true;

    protected array | Closure $quickSaveColors = [];

    protected string | Closure $quickSaveIcon = 'heroicon-o-plus';

    protected bool | Closure $hasQuickSaveNameHelperText = false;

    protected bool | Closure $hasQuickSaveFiltersHelperText = false;

    protected bool | Closure $hasQuickSavePublicHelperText = true;

    protected bool | Closure $hasQuickSaveFavoriteHelperText = true;

    protected bool | Closure $hasQuickSaveGlobalFavoriteHelperText = true;

    protected bool | Closure $hasQuickSaveActivePresetViewHelperText = true;

    protected bool | Closure $hasQuickSaveMakeFavorite = true;

    protected bool | Closure $hasQuickSaveMakePublic = true;

    protected bool | Closure $hasQuickSaveMakeGlobalFavorite = false;

    protected bool | Closure $hasQuickSaveIconSelect = true;

    protected bool | Closure $hasQuickSaveColorPicker = true;

    protected bool | Closure $includesOutlineIcons = true;

    protected bool | Closure $includesSolidIcons = true;

    public function quickSaveColors(array | Closure $colors): static
    {
        $this->quickSaveColors = $colors;

        return $this;
    }

    public function quickSaveInFavoritesBar(bool | Closure $condition = true, string | Closure $icon = 'heroicon-o-plus', string | Closure $position = 'end'): static
    {
        $this->isQuickSaveInFavoritesBar = $condition;

        $this->quickSaveIcon = $icon;

        $this->quickSavePosition = $position;

        return $this;
    }

    public function quickSaveInTable(bool | Closure $condition = true, string | Closure $icon = 'heroicon-o-plus', string | Closure $position = 'tables::toolbar.search.after'): static
    {
        $this->isQuickSaveInTable = $condition;

        $this->quickSaveIcon = $icon;

        $this->quickSaveTablePosition = $position;

        return $this;
    }

    public function quickSaveIcon(string | Closure $icon): static
    {
        $this->quickSaveIcon = $icon;

        return $this;
    }

    public function quickSaveSlideOver(bool | Closure $condition = true): static
    {
        $this->showQuickSaveAsSlideOver = $condition;

        return $this;
    }

    public function quickSaveNameHelperText(bool | Closure $condition = true): static
    {
        $this->hasQuickSaveNameHelperText = $condition;

        return $this;
    }

    public function quickSaveFiltersHelperText(bool | Closure $condition = true): static
    {
        $this->hasQuickSaveFiltersHelperText = $condition;

        return $this;
    }

    public function quickSavePublicHelperText(bool | Closure $condition = true): static
    {
        $this->hasQuickSavePublicHelperText = $condition;

        return $this;
    }

    public function quickSaveFavoriteHelperText(bool | Closure $condition = true): static
    {
        $this->hasQuickSaveFavoriteHelperText = $condition;

        return $this;
    }

    public function quickSaveGlobalFavoriteHelperText(bool | Closure $condition = true): static
    {
        $this->hasQuickSaveGlobalFavoriteHelperText = $condition;

        return $this;
    }

    public function quickSaveActivePresetViewHelperText(bool | Closure $condition = true): static
    {
        $this->hasQuickSaveActivePresetViewHelperText = $condition;

        return $this;
    }

    public function quickSaveMakeFavorite(bool | Closure $condition = true): static
    {
        $this->hasQuickSaveMakeFavorite = $condition;

        return $this;
    }

    public function quickSaveMakePublic(bool | Closure $condition = true): static
    {
        $this->hasQuickSaveMakePublic = $condition;

        return $this;
    }

    public function quickSaveMakeGlobalFavorite(bool | Closure $condition = true): static
    {
        $this->hasQuickSaveMakeGlobalFavorite = $condition;

        return $this;
    }

    public function quickSaveIconSelect(bool | Closure $condition = true): static
    {
        $this->hasQuickSaveIconSelect = $condition;

        return $this;
    }

    public function quickSaveIncludeOutlineIcons(bool | Closure $condition = true): static
    {
        $this->includesOutlineIcons = $condition;

        return $this;
    }

    public function quickSaveIncludeSolidIcons(bool | Closure $condition = true): static
    {
        $this->includesSolidIcons = $condition;

        return $this;
    }

    public function quickSaveColorPicker(bool | Closure $condition = true): static
    {
        $this->hasQuickSaveColorPicker = $condition;

        return $this;
    }

    public function isQuickSaveInFavoritesBar(): bool
    {
        return $this->evaluate($this->isQuickSaveInFavoritesBar);
    }

    public function isQuickSaveInTable(): bool
    {
        return $this->evaluate($this->isQuickSaveInTable);
    }

    public function quickSavePosition(): string
    {
        return $this->evaluate($this->quickSavePosition);
    }

    public function quickSaveTablePosition(): string
    {
        return $this->evaluate($this->quickSaveTablePosition);
    }

    public function showQuickSaveAsSlideOver(): bool
    {
        return $this->evaluate($this->showQuickSaveAsSlideOver);
    }

    public function getQuickSaveColors(): array
    {
        return $this->evaluate($this->quickSaveColors) ?: [
            'success',
            'info',
            'warning',
            'danger',
            'gray',
        ];
    }

    public function getQuickSaveIcon(): string
    {
        return $this->evaluate($this->quickSaveIcon);
    }

    public function hasQuickSaveNameHelperText(): bool
    {
        return $this->evaluate($this->hasQuickSaveNameHelperText);
    }

    public function hasQuickSaveFiltersHelperText(): bool
    {
        return $this->evaluate($this->hasQuickSaveFiltersHelperText);
    }

    public function hasQuickSavePublicHelperText(): bool
    {
        return $this->evaluate($this->hasQuickSavePublicHelperText);
    }

    public function hasQuickSaveFavoriteHelperText(): bool
    {
        return $this->evaluate($this->hasQuickSaveFavoriteHelperText);
    }

    public function hasQuickSaveGlobalFavoriteHelperText(): bool
    {
        return $this->evaluate($this->hasQuickSaveGlobalFavoriteHelperText);
    }

    public function hasQuickSaveActivePresetViewHelperText(): bool
    {
        return $this->evaluate($this->hasQuickSaveActivePresetViewHelperText);
    }

    public function hasQuickSaveMakeFavorite(): bool
    {
        return $this->evaluate($this->hasQuickSaveMakeFavorite);
    }

    public function hasQuickSaveMakePublic(): bool
    {
        return $this->evaluate($this->hasQuickSaveMakePublic);
    }

    public function hasQuickSaveMakeGlobalFavorite(): bool
    {
        return $this->evaluate($this->hasQuickSaveMakeGlobalFavorite);
    }

    public function hasQuickSaveIconSelect(): bool
    {
        return $this->evaluate($this->hasQuickSaveIconSelect);
    }

    public function includesOutlineIcons(): bool
    {
        return $this->evaluate($this->includesOutlineIcons);
    }

    public function includesSolidIcons(): bool
    {
        return $this->evaluate($this->includesSolidIcons);
    }

    public function hasQuickSaveColorPicker(): bool
    {
        return $this->evaluate($this->hasQuickSaveColorPicker);
    }
}
