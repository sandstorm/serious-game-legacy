<?php

namespace Archilex\AdvancedTables\Plugin\Concerns;

use Closure;
use Filament\Support\Concerns\EvaluatesClosures;

trait CanReorderColumns
{
    use EvaluatesClosures;

    protected bool|Closure $reorderableColumnsAlwaysDisplayHiddenLabel = false;

    protected bool|Closure $reorderableColumnsAreEnabled = true;

    protected string|Closure|null $reorderIcon = 'heroicon-m-arrows-up-down';

    protected string|Closure|null $checkMarkIcon = 'heroicon-m-check';

    protected string|Closure|null $dragHandleIcon = 'heroicon-o-bars-2';

    protected string|Closure|null $visibleIcon = 'heroicon-s-eye';

    protected string|Closure|null $hiddenIcon = 'heroicon-o-eye-slash';

    public function reorderableColumnsAlwaysDisplayHiddenLabel(bool|Closure $condition = true): static
    {
        $this->reorderableColumnsAlwaysDisplayHiddenLabel = $condition;

        return $this;
    }

    public function reorderableColumnsEnabled(bool|Closure $condition = true): static
    {
        $this->reorderableColumnsAreEnabled = $condition;

        return $this;
    }

    public function reorderIcon(string|Closure|null $icon = null): static
    {
        $this->reorderIcon = $icon;

        return $this;
    }

    public function checkMarkIcon(string|Closure|null $icon = null): static
    {
        $this->checkMarkIcon = $icon;

        return $this;
    }

    public function dragHandleIcon(string|Closure|null $icon = null): static
    {
        $this->dragHandleIcon = $icon;

        return $this;
    }

    public function visibleIcon(string|Closure|null $icon = null): static
    {
        $this->visibleIcon = $icon;

        return $this;
    }

    public function hiddenIcon(string|Closure|null $icon = null): static
    {
        $this->hiddenIcon = $icon;

        return $this;
    }

    public function reorderableColumnsShouldAlwaysDisplayHiddenLabel(): bool
    {
        return $this->evaluate($this->reorderableColumnsAlwaysDisplayHiddenLabel);
    }

    public function reorderableColumnsAreEnabled(): bool
    {
        return $this->evaluate($this->reorderableColumnsAreEnabled);
    }

    public function getReorderIcon(): ?string
    {
        return $this->evaluate($this->reorderIcon);
    }

    public function getCheckMarkIcon(): ?string
    {
        return $this->evaluate($this->checkMarkIcon);
    }

    public function getDragHandleIcon(): ?string
    {
        return $this->evaluate($this->dragHandleIcon);
    }

    public function getVisibleIcon(): ?string
    {
        return $this->evaluate($this->visibleIcon);
    }

    public function getHiddenIcon(): ?string
    {
        return $this->evaluate($this->hiddenIcon);
    }
}
