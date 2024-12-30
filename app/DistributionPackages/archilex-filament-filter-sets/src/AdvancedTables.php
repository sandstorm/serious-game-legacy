<?php

namespace Archilex\AdvancedTables;

use Archilex\AdvancedTables\Components\PresetView;
use Archilex\AdvancedTables\Concerns\CanPersistViews;
use Archilex\AdvancedTables\Concerns\CanReorderColumns;
use Archilex\AdvancedTables\Concerns\CanReorderViews;
use Archilex\AdvancedTables\Concerns\HasDefaultView;
use Archilex\AdvancedTables\Concerns\HasFormSchemas;
use Archilex\AdvancedTables\Concerns\HasPresetViews;
use Archilex\AdvancedTables\Concerns\HasUserViews;
use Archilex\AdvancedTables\Concerns\HasViewActions;
use Archilex\AdvancedTables\Forms\Components\AdvancedFilterBuilder;
use Archilex\AdvancedTables\Support\Config;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Facades\FilamentView;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

trait AdvancedTables
{
    use CanPersistViews;
    use CanReorderColumns;
    use CanReorderViews;
    use HasDefaultView;
    use HasFormSchemas;
    use HasPresetViews;
    use HasUserViews;
    use HasViewActions;

    public bool $isMounted = false;

    public function bootedAdvancedTables()
    {
        $this->normalizeActiveViews();

        if (static::favoritesBarIsEnabled()) {
            FilamentView::registerRenderHook(
                $this->getHookName(),
                fn (): View => view('advanced-tables::components.favorites-bar.index'),
                $this->getResourceName()
            );
        }

        if (Config::isQuickSaveInTable()) {
            FilamentView::registerRenderHook(
                Config::quickSaveTablePosition(),
                fn (): View => view('advanced-tables::components.quick-save.button'),
                get_class($this)
            );
        }

        if (Config::isViewManagerInTable()) {
            FilamentView::registerRenderHook(
                Config::viewManagerTablePosition(),
                fn (): View => view('advanced-tables::components.view-manager.button'),
                get_class($this)
            );
        }

        if ($this->canReorderColumns() && ! $this->getTable()->hasColumnsLayout()) {
            $orderedColumnsSessionKey = $this->getOrderedTableColumnToggleFormStateSessionKey();

            if (empty($this->orderedToggledTableColumns) && session()->has($orderedColumnsSessionKey)) {
                $this->orderedToggledTableColumns = [
                    ...($this->orderedToggledTableColumns ?? []),
                    ...(session()->get($orderedColumnsSessionKey) ?? []),
                ];
            }

            $this->setDefaultToggledTableColumnsOrder();

            $this->applyToggledTableColumnsOrder();
        }

        if ($this->isMounted) {
            return;
        }

        $shouldPersistActiveViewInSession = Config::persistsActiveViewInSession();

        $activePresetViewSessionKey = $this->getActivePresetViewSessionKey();

        if (
            is_null($this->activePresetView) &&
            $shouldPersistActiveViewInSession &&
            session()->has($activePresetViewSessionKey)
        ) {
            $this->activePresetView = session()->get($activePresetViewSessionKey);

            $this->applyPresetViewConfiguration();
        }

        $activeUserViewSessionKey = $this->getActiveUserViewSessionKey();

        if (
            is_null($this->activeUserView) &&
            $shouldPersistActiveViewInSession &&
            session()->has($activeUserViewSessionKey)
        ) {
            $this->activeUserView = session()->get($activeUserViewSessionKey);
        }

        $defaultViewIsActiveSessionKey = $this->getDefaultViewIsActiveSessionKey();

        if (
            $shouldPersistActiveViewInSession &&
            session()->has($defaultViewIsActiveSessionKey)
        ) {
            $this->defaultViewIsActive = session()->get($defaultViewIsActiveSessionKey);
        }

        if (
            filled($defaultPresetViewName = $this->getDefaultPresetViewName()) &&
            $this->defaultPresetViewShouldBeApplied()
        ) {
            $this->activePresetView = $defaultPresetViewName;

            $this->applyPresetViewConfiguration();
        }

        if (filled($activeUserView = $this->activeUserView)) {
            $this->loadUserView(userView: $activeUserView, resetTable: false);
        } elseif (filled($activePresetView = $this->activePresetView)) {
            $this->loadPresetView(presetView: $activePresetView, resetTable: false);
        }

        if (
            filled($this->activePresetView) ||
            $this->activeUserView ||
            ! $this->tableInDefaultState()
        ) {
            $this->defaultViewIsActive = false;
        }

        $this->isMounted = true;
    }

    public function updatedToggledTableColumns(): void
    {
        $this->orderedToggledTableColumns = array_merge(
            $this->orderedToggledTableColumns,
            Arr::dot($this->toggledTableColumns)
        );

        $this->persistOrderedTableColumnsToSession();

        $this->saveModifiedDefaultPresetViewColumnsToSession();

        $this->resetActiveViewsIfRequired();

        parent::updatedToggledTableColumns();
    }

    public function updatedTableFilters(): void
    {
        $this->resetActiveViewsIfRequired();

        parent::updatedTableFilters();
    }

    public function applyTableFilters(): void
    {
        $this->resetActiveViewsIfRequired();

        parent::applyTableFilters();
    }

    public function updatedTableGrouping(): void
    {
        $this->resetActiveViewsIfRequired();
    }

    public function updatedTableGroupingDirection(): void
    {
        $this->resetActiveViewsIfRequired();
    }

    public function updatedTableSortColumn(): void
    {
        $this->resetActiveViewsIfRequired();

        parent::updatedTableSortColumn();
    }

    public function updatedTableSortColumnDirection(): void
    {
        $this->resetActiveViewsIfRequired();
    }

    public function updatedTableSearch(): void
    {
        $this->resetActiveViewsIfRequired();

        parent::updatedTableSearch();
    }

    /**
     * @param  string | null  $value
     */
    public function updatedTableColumnSearches($value = null, ?string $key = null): void
    {
        $this->resetActiveViewsIfRequired();

        parent::updatedTableColumnSearches($value, $key);
    }

    public function removeTableFilter(string $filterName, ?string $field = null, bool $isRemovingAllFilters = true): void
    {
        $filter = $this->getTable()->getFilter($filterName);
        $filterResetState = $filter->getResetState();

        $filterFields = $this->getFilterFields($filterName, $field);

        foreach ($filterFields as $fieldName => $field) {
            if ($field instanceof AdvancedFilterBuilder) {
                continue;
            }

            $state = $field->getState();

            $field->state($filterResetState[$fieldName] ?? match (true) {
                is_array($state) => [],
                is_bool($state) => false,
                default => null,
            });
        }

        if (! $isRemovingAllFilters) {
            return;
        }

        if ($this->getTable()->hasDeferredFilters()) {
            $this->applyTableFilters();

            return;
        }

        $this->resetActiveViewsIfRequired();

        $this->handleTableFilterUpdates();
    }

    protected function getFilterFields(string $filterName, ?string $field): ?array
    {
        $filterFormGroup = $this->getTableFiltersForm()->getComponent($filterName) ?? null;

        if (! $filterFormGroup) {
            return null;
        }

        $filterFields = $filterFormGroup?->getChildComponentContainer()->getFlatFields();

        $isSingleIndicator = collect($filterFields)->keys()->contains(function ($key) use ($field) {
            return Str::startsWith($key, Str::beforeLast($field, '.'));
        });

        if (! filled($field) || (! array_key_exists($field, $filterFields) && ! $isSingleIndicator)) {
            return $filterFields;
        }

        if (Str::afterLast($field, '.') === 'operator' || (! array_key_exists($field, $filterFields) && $isSingleIndicator)) {
            $filterKey = Str::beforeLast($field, '.');

            return Arr::where(
                $filterFields,
                fn ($value, $key) => $filterKey === Str::beforeLast($key, '.')
            );
        }

        return [$field => $filterFields[$field]];
    }

    public function resetTable(): void
    {
        $this->activePresetView = null;

        $this->currentPresetView = null;

        $this->activeUserView = null;

        $this->cacheForms();

        $this->bootedInteractsWithTable();

        $this->resetTableFilterForm();

        $this->resetPage();

        $this->flushCachedTableRecords();
    }

    public function resetTableFiltersForm(): void
    {
        $this->getTableFiltersForm()->fill();

        if ($this->getTable()->hasDeferredFilters()) {
            $this->applyTableFilters();

            return;
        }

        $this->resetActiveViewsIfRequired();

        $this->handleTableFilterUpdates();
    }

    public function resetTableToDefault(): void
    {
        $this->resetTable();

        $this->tableGrouping = $this->getDefaultTableGrouping();
        $this->tableGroupingDirection = null;
        $this->tableSortColumn = $this->getDefaultTableSortColumn();
        $this->tableSortDirection = $this->getDefaultTableSortDirection();
        $this->toggledTableColumns = $this->getDefaultTableColumnToggleState();
        $this->orderedToggledTableColumns = $this->getDefaultToggledTableColumnsOrder();
        $this->tableColumnSearches = [];

        $this->applyToggledTableColumnsOrder();

        $this->persistToSessionIfEnabled(resetTableToDefault: true);

        $this->defaultViewIsActive = true;
    }

    public function getPresetViewsForm(Collection $presetViews): ComponentContainer
    {
        return $this->makeForm()
            ->schema([
                Select::make('activePresetView')
                    ->hiddenLabel()
                    ->allowHtml()
                    ->searchable()
                    ->placeholder(__('advanced-tables::advanced-tables.select.placeholder'))
                    ->options(
                        $presetViews
                            ->map(
                                fn (PresetView $presetView, $presetViewName) => $presetView->getLabel() ?? $this->generatePresetViewLabel($presetViewName)
                            )
                            ->toArray()
                    ),
            ])
            ->reactive();
    }

    public static function favoritesBarIsEnabled(): bool
    {
        return Config::favoritesBarIsEnabled();
    }

    protected function applyFiltersToTableQuery(Builder $query): Builder
    {
        if ($presetView = $this->getActivePresetView()) {
            $presetView->modifyQuery($query);
        }

        return parent::applyFiltersToTableQuery($query);
    }

    protected function getDefaultTableGrouping(): ?string
    {
        if ($this->getTable()->isDefaultGroupSelectable()) {
            return $this->getTable()->getDefaultGroup()->getId();
        }

        return null;
    }

    protected function getHookName(): string
    {
        if ($this->isRelationManager()) {
            return 'panels::resource.relation-manager.before';
        }

        if ($this->isTableWidget()) {
            return 'widgets::table-widget.start';
        }

        if ($this->isManageRelatedRecords()) {
            return 'panels::resource.pages.manage-related-records.table.before';
        }

        if ($this instanceof \RalphJSmit\Filament\RecordFinder\Livewire\RecordFinderTable) {
            return 'record-finder::livewire.record-finder-table.before';
        }

        return 'panels::resource.pages.list-records.table.before';
    }

    protected function getResourceName(): string
    {
        if ($this->isResource()) {
            return $this->getResource();
        }

        return get_class($this);
    }

    protected function hasActiveFilters()
    {
        if ($this->getTable()->hasSearch()) {
            return true;
        }

        return collect($this->getTable()->getFilters())
            ->filter(fn (\Filament\Tables\Filters\BaseFilter $filter) => $filter->getIndicators())
            ->isNotEmpty();
    }

    protected function isResource(): bool
    {
        return is_subclass_of($this, ListRecords::class);
    }

    protected function isRelationManager(): bool
    {
        return is_subclass_of($this, RelationManager::class);
    }

    protected function isTableWidget(): bool
    {
        return is_subclass_of($this, TableWidget::class);
    }

    protected function isManageRelatedRecords(): bool
    {
        return is_subclass_of($this, ManageRelatedRecords::class);
    }

    public function resetActiveViewsIfRequired(): void
    {
        $this->defaultViewIsActive = false;

        $this->activeUserView = null;

        $persistActiveViewInSession = Config::persistsActiveViewInSession();

        // If the preset view modifies the query then we should keep
        // the view active so that the user knows that query
        // that corresponds to that view is still being applied.
        if (! $this->getActivePresetView()?->modifiesQuery()) {
            $this->activePresetView = null;
        }

        if ($persistActiveViewInSession) {
            session()->put(
                $this->getActivePresetViewSessionKey(),
                $this->activePresetView,
            );

            session()->put(
                $this->getActiveUserViewSessionKey(),
                $this->activeUserView,
            );

            session()->put(
                $this->getDefaultViewIsActiveSessionKey(),
                $this->defaultViewIsActive,
            );
        }
    }

    protected function resetTableFilterForm(): void
    {
        $this->getTableFiltersForm()->fill();

        if ($this->getTable()->hasDeferredFilters()) {
            $this->applyTableFilters();
        }
    }

    // Temporary fix until LW fixes null values in query string
    protected function normalizeActiveViews(): void
    {
        if ($this->activePresetView === 'null') {
            $this->activePresetView = null;
        }

        if ($this->activeUserView === 'null') {
            $this->activeUserView = null;
        }
    }
}
