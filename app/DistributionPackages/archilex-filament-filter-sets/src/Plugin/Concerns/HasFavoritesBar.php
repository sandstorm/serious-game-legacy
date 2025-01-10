<?php

namespace Archilex\AdvancedTables\Plugin\Concerns;

use Archilex\AdvancedTables\Enums\FavoritesBarTheme;
use Closure;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\IconPosition;

trait HasFavoritesBar
{
    use EvaluatesClosures;

    protected bool|Closure $favoritesBarIsEnabled = true;

    protected string|FavoritesBarTheme|Closure $favoritesBarTheme = FavoritesBarTheme::Github;

    protected string|Closure|null $favoritesBarDefaultIcon = 'heroicon-o-bars-4';

    protected bool|Closure $favoritesBarHasDefaultView = true;

    protected bool|Closure $favoritesBarHasDivider = true;

    protected string|IconPosition|Closure $favoritesBarIconPosition = IconPosition::Before;

    protected string|ActionSize|Closure $favoritesBarSize = ActionSize::Medium;

    protected bool|Closure $favoritesBarHasLoadingIndicator = false;

    public function favoritesBarEnabled(bool|Closure $condition = true): static
    {
        $this->favoritesBarIsEnabled = $condition;

        return $this;
    }

    public function favoritesBarTheme(string|FavoritesBarTheme|Closure $theme): static
    {
        $this->favoritesBarTheme = $theme;

        return $this;
    }

    public function favoritesBarDefaultIcon(string|Closure|null $icon = null): static
    {
        $this->favoritesBarDefaultIcon = $icon;

        return $this;
    }

    public function favoritesBarDefaultView(bool|Closure $condition = true): static
    {
        $this->favoritesBarHasDefaultView = $condition;

        return $this;
    }

    public function favoritesBarDivider(bool|Closure $condition = true): static
    {
        $this->favoritesBarHasDivider = $condition;

        return $this;
    }

    public function favoritesBarIconPosition(string|IconPosition|Closure $position = IconPosition::Before): static
    {
        $this->favoritesBarIconPosition = $position;

        return $this;
    }

    public function favoritesBarSize(string|ActionSize|Closure $size = ActionSize::Medium): static
    {
        $this->favoritesBarSize = $size;

        return $this;
    }

    public function favoritesBarLoadingIndicator(bool|Closure $condition = true): static
    {
        $this->favoritesBarHasLoadingIndicator = $condition;

        return $this;
    }

    public function getFavoritesBarTheme(): string|FavoritesBarTheme
    {
        return $this->evaluate($this->favoritesBarTheme);
    }

    public function getFavoritesBarDefaultIcon(): ?string
    {
        return $this->evaluate($this->favoritesBarDefaultIcon);
    }

    public function getFavoritesBarIconPosition(): string|IconPosition|null
    {
        return $this->evaluate($this->favoritesBarIconPosition);
    }

    public function getFavoritesBarSize(): string|ActionSize|null
    {
        return $this->evaluate($this->favoritesBarSize);
    }

    public function favoritesBarIsEnabled(): bool
    {
        return $this->evaluate($this->favoritesBarIsEnabled);
    }

    public function favoritesBarHasDefaultView(): bool
    {
        return $this->evaluate($this->favoritesBarHasDefaultView);
    }

    public function favoritesBarHasDivider(): bool
    {
        return $this->evaluate($this->favoritesBarHasDivider);
    }

    public function favoritesBarHasLoadingIndicator(): bool
    {
        return $this->evaluate($this->favoritesBarHasLoadingIndicator);
    }
}
