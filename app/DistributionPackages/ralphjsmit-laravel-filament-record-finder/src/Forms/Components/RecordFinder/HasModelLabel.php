<?php

namespace RalphJSmit\Filament\RecordFinder\Forms\Components\RecordFinder;

use Closure;

trait HasModelLabel
{
    protected null | string | Closure $modelLabel = null;

    protected null | string | Closure $pluralModelLabel = null;

    public function modelLabel(string | Closure | null $label = null): static
    {
        $this->modelLabel = $label;

        return $this;
    }

    public function pluralModelLabel(string | Closure | null $label = null): static
    {
        $this->pluralModelLabel = $label;

        return $this;
    }

    public function getModelLabel(): string
    {
        return $this->evaluate($this->modelLabel)
            ?? (string) str($this->getName())->remove('_id')->kebab()->replace('_', ' ')->replace('-', ' ');
    }

    public function getPluralModelLabel(): string
    {
        return $this->evaluate($this->modelLabel) ?? (string) str($this->getModelLabel())->plural();
    }
}
