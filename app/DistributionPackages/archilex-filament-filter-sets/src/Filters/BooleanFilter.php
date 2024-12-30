<?php

namespace Archilex\AdvancedTables\Filters;

use Archilex\AdvancedTables\Filters\Concerns\HasFiltersLayout;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\TernaryFilter;
use Livewire\Component as Livewire;

class BooleanFilter extends TernaryFilter
{
    use HasFiltersLayout;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function getFormSchema(): array
    {
        return [
            Grid::make()
                ->columns([
                    'sm' => 4,
                    'lg' => 4,
                ])
                ->extraAttributes(['class' => 'advanced-tables-filter-block advanced-tables-filter-block-boolean-filter'])
                ->schema([
                    $this->getFormField()
                        ->extraAttributes(['class' => 'advanced-tables-filter-operator advanced-tables-filter-operator-boolean-filter'])
                        ->columnSpan([
                            'sm' => fn (Livewire $livewire) => $this->hasWideLayout($livewire) ? 1 : 4,
                        ]),
                ]),

        ];
    }
}
