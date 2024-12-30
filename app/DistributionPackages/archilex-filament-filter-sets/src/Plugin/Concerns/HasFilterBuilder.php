<?php

namespace Archilex\AdvancedTables\Plugin\Concerns;

use Closure;
use Filament\Support\Concerns\EvaluatesClosures;

trait HasFilterBuilder
{
    use EvaluatesClosures;

    protected array | Closure $expandViewStyles = ['right: 80px', 'top: 24px'];

    public function filterBuilderExpandViewStyles(array | Closure $styles): static
    {
        $this->expandViewStyles = $styles;

        return $this;
    }

    public function getExpandViewStyles(): array
    {
        return $this->evaluate($this->expandViewStyles);
    }
}
