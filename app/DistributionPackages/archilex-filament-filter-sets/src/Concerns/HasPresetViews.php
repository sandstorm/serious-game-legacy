<?php

namespace Archilex\AdvancedTables\Concerns;

use Archilex\AdvancedTables\Components\PresetView;
use Archilex\AdvancedTables\Support\Config;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

trait HasPresetViews
{
    #[Url]
    public ?string $activePresetView = null;

    #[Url]
    public ?string $currentPresetView = null;

    /**
     * @var array<string | int, PresetView>
     */
    protected array $cachedPresetViews;

    /**
     * @var array<string, bool> | null
     */
    protected ?array $cachedOrderedToggledTableColumns = null;

    // Update on dropdown select
    public function updatedActivePresetView($value): void
    {
        $this->removeTableFilters();

        $this->activeUserView = null;

        if (! $value) {
            $this->defaultViewIsActive = true;

            return;
        }

        $this->activePresetView = $value;

        $this->applyPresetViewConfiguration();
    }

    public function loadPresetView(string $presetView, ?array $filters = null, bool $resetTable = true): void
    {
        if (
            Arr::exists($this->getCachedPresetViews(), $presetView) &&
            ! $this->getCachedPresetViews()[$presetView]->shouldPreserveFilters()
        ) {
            if ($resetTable) {
                $this->resetTable();
            }
        }

        $this->currentPresetView = $presetView;

        $this->activePresetView = $presetView;

        $this->applyPresetViewConfiguration();

        $this->saveModifiedDefaultPresetViewColumnsToSession(false);
    }

    /**
     * @return array<string | int, PresetView>
     */
    public function getPresetViews(): array
    {
        return [];
    }

    /**
     * @return array<string | int, PresetView>
     */
    public function getCachedPresetViews(): array
    {
        return $this->cachedPresetViews ??= $this->getPresetViews();
    }

    public function getPresetViewsArray(): array
    {
        $presetViews = $this->getMergedPresetViews();

        return [
            'hiddenPresetViews' => $this->buildHiddenPresetViewsFrom($presetViews),
            'favoritePresetViews' => $this->buildFavoritePresetViewsFrom($presetViews),
        ];
    }

    public function generatePresetViewLabel(string $key): string
    {
        return (string) str($key)
            ->replace(['_', '-'], ' ')
            ->ucfirst();
    }

    public function getModifiedDefaultPresetViewColumnsSessionKey(): string
    {
        $table = class_basename($this::class);

        return "tables.{$table}_modified_default_preset_view_columns";
    }

    protected function applyPresetViewConfiguration()
    {
        $this->defaultViewIsActive = false;

        $this->tableSearch = '';
        $this->tableGrouping = $this->getGroupingFromPresetView() ?? $this->getDefaultTableGrouping();
        $this->tableGroupingDirection = $this->getGroupingDirectionFromPresetView() ?? null;
        $this->tableSortColumn = $this->getSortColumnFromPresetView() ?? $this->getDefaultTableSortColumn();
        $this->tableSortDirection = $this->getSortDirectionFromPresetView() ?? $this->getDefaultTableSortDirection();
        $this->toggledTableColumns = $this->getToggledColumnsFromPresetView() ?? $this->getDefaultTableColumnToggleState();
        $this->orderedToggledTableColumns = $this->getOrderedToggledColumnsFromPresetView() ?? $this->getDefaultToggledTableColumnsOrder();
        $this->tableFilters = $this->getFiltersFromPresetView() ?? $this->tableFilters;

        parent::updatedToggledTableColumns();

        $this->applyToggledTableColumnsOrder();

        $this->getTableFiltersForm()->fill($this->tableFilters);

        $this->persistToSessionIfEnabled();
    }

    protected function getDefaultPresetViewName(): ?string
    {
        return collect($this->getCachedPresetViews())
            ->filter(fn (PresetView $presetView) => $presetView->isDefault())
            ->keys()
            ->first();
    }

    protected function getMergedPresetViews(): Collection
    {
        $presetViews = collect($this->getCachedPresetViews());

        if (! Config::canManagePresetViews()) {
            return $presetViews;
        }

        $presetViewsManagedByCurrentUser = $this->getPresetViewsManagedByCurrentUser();

        if ($presetViewsManagedByCurrentUser->isEmpty()) {
            return $presetViews;
        }

        return $presetViews->map(function (PresetView $presetView, $presetViewName) use ($presetViewsManagedByCurrentUser) {
            $presetViewManagedByCurrentUser = $presetViewsManagedByCurrentUser->firstWhere('name', $presetViewName);

            if (! $presetViewManagedByCurrentUser) {
                return $presetView;
            }

            return $presetView
                ->managedByCurrentUser(true)
                ->managedByCurrentUserId($presetViewManagedByCurrentUser->id)
                ->favoritedByCurrentUser($presetViewManagedByCurrentUser->is_favorite)
                ->managedByCurrentUserSortOrder($presetViewManagedByCurrentUser->sort_order);
        })
            ->sortBy($this->getPresetViewSortByArray());
    }

    public function getFavoritePresetViewsFromPresetViews(): Collection
    {
        $presetViews = $this->getMergedPresetViews();

        return $this->buildFavoritePresetViewsFrom($presetViews);
    }

    public function defaultPresetViewShouldBeApplied(): bool
    {
        if (filled($this->activePresetView) || filled($this->activeUserView)) {
            return false;
        }

        if (! $this->defaultViewIsActive) {
            return false;
        }

        $activePresetViewSessionKey = $this->getActivePresetViewSessionKey();

        if (Config::persistsActiveViewInSession() && session()->has($activePresetViewSessionKey)) {
            return false;
        }

        if (Config::persistsActiveViewInSession() && ! session()->has($activePresetViewSessionKey)) {
            return true;
        }

        if (
            $this->getTable()->persistsFiltersInSession() ||
            $this->getTable()->persistsSearchInSession() ||
            $this->getTable()->persistsSortInSession() ||
            $this->getTable()->persistsColumnSearchesInSession()
        ) {
            return false;
        }

        $defaultPresetViewName = $this->getDefaultPresetViewName();

        if (
            $defaultPresetViewName &&
            ! $this->getCachedPresetViews()[$defaultPresetViewName]->shouldPreserveColumns() &&
            session()->get($this->getModifiedDefaultPresetViewColumnsSessionKey(), false)
        ) {
            return false;
        }

        if (request()->getQueryString()) {
            return false;
        }

        return true;
    }

    public function saveModifiedDefaultPresetViewColumnsToSession($condition = true): void
    {
        if ($this->activePresetView === $this->getDefaultPresetViewName()) {
            session()->put($this->getModifiedDefaultPresetViewColumnsSessionKey(), $condition);
        }
    }

    protected function buildFavoritePresetViewsFrom(Collection $presetViews): Collection
    {
        return $presetViews->filter(function (PresetView $presetView) {
            return
                $presetView->isVisible() &&
                (
                    (! $presetView->isManagedByCurrentUser() && $presetView->isFavorite()) ||
                    ($presetView->isManagedByCurrentUser() && $presetView->isFavoritedByCurrentUser())
                );
        })
            ->sortBy($this->getPresetViewSortByArray());
    }

    protected function buildHiddenPresetViewsFrom(Collection $presetViews): Collection
    {
        return $presetViews->filter(function (PresetView $presetView) {
            return
                $presetView->isVisible() &&
                (
                    (! $presetView->isManagedByCurrentUser() && ! $presetView->isFavorite()) ||
                    ($presetView->isManagedByCurrentUser() && ! $presetView->isFavoritedByCurrentUser())
                );
        })
            ->sortBy($this->getPresetViewSortByArray());
    }

    protected function getActivePresetView(): ?PresetView
    {
        $presetViews = $this->getCachedPresetViews();

        if (
            filled($this->activePresetView) &&
            Arr::exists($this->getCachedPresetViews(), $this->activePresetView)
        ) {
            return $presetViews[$this->activePresetView];
        }

        return null;
    }

    protected function getPresetViewsManagedByCurrentUser()
    {
        return once(
            fn () => Config::getManagedPresetView()::query()
                ->belongsToCurrentUser()
                ->where('resource', $this->getResourceName())
                ->orderBy('sort_order', 'asc')
                ->get()
        );
    }

    protected function getPresetViewSortByArray(): array
    {
        return [
            fn (PresetView $a, PresetView $b) => Config::getNewPresetViewSortPosition() === 'after' ? $b->isManagedByCurrentUser() <=> $a->isManagedByCurrentUser() : $a->isManagedByCurrentUser() <=> $b->isManagedByCurrentUser(),
            fn (PresetView $a, PresetView $b) => $a->getManagedByCurrentUserSortOrder() <=> $b->getManagedByCurrentUserSortOrder(),
        ];
    }

    protected function getFiltersFromPresetView(): ?array
    {
        $presetView = $this->getActivePresetView();

        if (! $presetView) {
            return null;
        }

        if ($presetView->shouldPreserveFilters()) {
            return $this->tableFilters;
        }

        $filters = $presetView->getDefaultFilters();

        if (empty($filters)) {
            return null;
        }

        if (Arr::exists($filters, 'advanced_filter_builder')) {
            $advancedFilterBuilderArray = [];

            foreach ($filters['advanced_filter_builder'] as $index => $advancedFilters) {
                $advancedFilterBuilderArray['or_group'][$index] = [
                    'type' => 'filter_group',
                ];

                foreach ($advancedFilters as $filter => $value) {
                    $advancedFilterBuilderArray['or_group'][$index]['data']['and_group'][] = [
                        'type' => $filter,
                        'data' => $value,
                    ];
                }
            }

            $filters['advanced_filter_builder'] = $advancedFilterBuilderArray;

            // Add an additional array to show the next or_group
            $filters['advanced_filter_builder']['or_group'][] = [
                'type' => 'filter_group',
                'data' => [
                    'and_group' => [],
                ],
            ];
        }

        return $this->tableFilters = $filters;
    }

    protected function getGroupingFromPresetView(): ?string
    {
        $presetView = $this->getActivePresetView();

        if (! $presetView) {
            return null;
        }

        if ($presetView->shouldPreserveGrouping()) {
            return $this->tableGrouping;
        }

        $grouping = $presetView->getDefaultGrouping();

        if (empty($grouping)) {
            return null;
        }

        return $grouping;
    }

    protected function getSortColumnFromPresetView(): ?string
    {
        $presetView = $this->getActivePresetView();

        if (! $presetView) {
            return null;
        }

        if ($presetView->shouldPreserveSortColumn()) {
            return $this->tableSortColumn;
        }

        $sortColumn = $presetView->getDefaultSortColumn();

        if (empty($sortColumn)) {
            return null;
        }

        return $sortColumn;
    }

    protected function getGroupingDirectionFromPresetView(): ?string
    {
        $presetView = $this->getActivePresetView();

        if (! $presetView) {
            return null;
        }

        if ($presetView->shouldPreserveGroupingDirection()) {
            return $this->tableGroupingDirection;
        }

        $groupingDirection = $presetView->getDefaultGroupingDirection();

        if (empty($groupingDirection)) {
            return null;
        }

        return $groupingDirection;
    }

    protected function getSortDirectionFromPresetView(): ?string
    {
        $presetView = $this->getActivePresetView();

        if (! $presetView) {
            return null;
        }

        if ($presetView->shouldPreserveSortDirection()) {
            return $this->tableSortDirection;
        }

        $sortDirection = $presetView->getDefaultSortDirection();

        if (empty($sortDirection)) {
            return null;
        }

        return $sortDirection;
    }

    protected function getToggledColumnsFromPresetView(): ?array
    {
        $presetView = $this->getActivePresetView();

        if (! $presetView) {
            return null;
        }

        if ($presetView->shouldPreserveColumns()) {
            return $this->toggledTableColumns;
        }

        return Arr::undot($this->getOrderedToggledColumnsFromPresetView() ?? $this->getDefaultTableColumnToggleState());
    }

    protected function getOrderedToggledColumnsFromPresetView(): ?array
    {
        if ($this->cachedOrderedToggledTableColumns) {
            return $this->cachedOrderedToggledTableColumns;
        }

        $presetView = $this->getActivePresetView();

        if (! $presetView) {
            return null;
        }

        if ($presetView->shouldPreserveColumns()) {
            return $this->orderedToggledTableColumns;
        }

        $columns = $presetView->getDefaultColumns();

        if (empty($columns)) {
            return null;
        }

        $state = [];

        foreach ($this->getTable()->getColumns() as $column) {
            if ($column->isHidden()) {
                continue;
            }

            if (! $column->isToggleable()) {
                $state[$column->getName()] = true;

                continue;
            }

            $value = in_array($column->getName(), $columns) ? true : false;

            $state[$column->getName()] = $value;
        }

        return $this->cachedOrderedToggledTableColumns = array_merge(
            array_flip($columns),
            $state
        );
    }
}
