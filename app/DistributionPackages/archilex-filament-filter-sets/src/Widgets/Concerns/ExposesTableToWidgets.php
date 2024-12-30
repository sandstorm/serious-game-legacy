<?php

namespace Archilex\AdvancedTables\Widgets\Concerns;

use Filament\Pages\Concerns\ExposesTableToWidgets as FilamentExposesTableToWidgets;

trait ExposesTableToWidgets
{
    use FilamentExposesTableToWidgets;

    public function getWidgetData(): array
    {
        return [
            'activePresetView' => $this->activePresetView,
            'currentPresetView' => $this->currentPresetView,
            'activeTab' => $this->activeTab,
            'paginators' => $this->paginators,
            'tableColumnSearches' => $this->tableColumnSearches,
            'tableFilters' => $this->tableFilters,
            'tableGrouping' => $this->tableGrouping,
            'tableGroupingDirection' => $this->tableGroupingDirection,
            'tableRecordsPerPage' => $this->tableRecordsPerPage,
            'tableSearch' => $this->tableSearch,
            'tableSortColumn' => $this->tableSortColumn,
            'tableSortDirection' => $this->tableSortDirection,
        ];
    }
}
