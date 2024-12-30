<?php

namespace Archilex\AdvancedTables\Widgets\Concerns;

use Filament\Tables\Contracts\HasTable;
use Filament\Widgets\Concerns\InteractsWithPageTable as FilamentInteractsWithPageTable;
use Livewire\Attributes\Reactive;

use function Livewire\trigger;

trait InteractsWithPageTable
{
    use FilamentInteractsWithPageTable;

    #[Reactive]
    public ?string $activePresetView = null;

    #[Reactive]
    public ?string $currentPresetView = null;

    protected function getTablePageInstance(): HasTable
    {
        if (isset($this->tablePage)) {
            return $this->tablePage;
        }

        /** @var HasTable $tableComponent */
        $page = app('livewire')->new($this->getTablePage());
        trigger('mount', $page, [], null, null);

        $page->activePresetView = $this->activePresetView;
        $page->currentPresetView = $this->currentPresetView;
        $page->activeTab = $this->activeTab;
        $page->paginators = $this->paginators;
        $page->tableColumnSearches = $this->tableColumnSearches;
        $page->tableFilters = $this->tableFilters;
        $page->tableGrouping = $this->tableGrouping;
        $page->tableGroupingDirection = $this->tableGroupingDirection;
        $page->tableRecordsPerPage = $this->tableRecordsPerPage;
        $page->tableSearch = $this->tableSearch;
        $page->tableSortColumn = $this->tableSortColumn;
        $page->tableSortDirection = $this->tableSortDirection;

        return $this->tablePage = $page;
    }
}
