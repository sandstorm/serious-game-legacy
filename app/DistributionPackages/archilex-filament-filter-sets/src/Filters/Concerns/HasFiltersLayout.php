<?php

namespace Archilex\AdvancedTables\Filters\Concerns;

use Filament\Tables\Enums\FiltersLayout;
use Livewire\Component as Livewire;

trait HasFiltersLayout
{
    protected function hasWideLayout(Livewire $livewire): bool
    {
        $layout = $livewire->getTable()->getFiltersLayout();

        if (in_array($layout, [
            FiltersLayout::AboveContent,
            FiltersLayout::AboveContentCollapsible,
            FiltersLayout::BelowContent,
        ])) {
            return true;
        }

        if (
            $layout === FiltersLayout::Modal &&
            ! $livewire->getTable()->getFiltersTriggerAction()->isModalSlideOver()
        ) {
            return true;
        }

        return false;
    }
}
