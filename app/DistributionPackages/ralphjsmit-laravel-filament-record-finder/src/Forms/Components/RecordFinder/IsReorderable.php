<?php

namespace RalphJSmit\Filament\RecordFinder\Forms\Components\RecordFinder;

use Closure;

trait IsReorderable
{
    protected bool | Closure $isReorderable = false;

    protected string | Closure | null $relationshipReorderColumn = null;

    public function reorderable(bool | Closure $condition = true, string | Closure | null $relationshipReorderColumn = null): static
    {
        $this->isReorderable = $condition;
        $this->relationshipReorderColumn = $relationshipReorderColumn;

        return $this;
    }

    public function isReorderable(): bool
    {
        return $this->evaluate($this->isReorderable);
    }

    public function getRelationshipReorderColumn(): ?string
    {
        return $this->evaluate($this->relationshipReorderColumn);
    }
}
