<?php

namespace Archilex\AdvancedTables\Concerns;

use Archilex\AdvancedTables\Support\Config;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;

trait HasUserViews
{
    #[Url]
    public ?string $activeUserView = null;

    public function loadUserView(int $userView, ?array $filters = null, bool $resetTable = true): void
    {
        if (is_null($filters)) {
            $activeUserView = Config::getUserView()::query()
                ->where('id', $userView)
                ->where('resource', $this->getResourceName())
                ->first();

            $filters = $activeUserView?->filters ?? [];
        }

        if ($resetTable) {
            $this->resetTable();
        }

        $this->defaultViewIsActive = false;

        $this->activeUserView = $userView;

        if ($presetView = $filters['activeSet'] ?? null) {
            $this->activePresetView = $presetView;
        }

        $this->tableFilters = $this->mergeTableFilters($filters['tableFilters'] ?? []);
        $this->tableSearch = $filters['tableSearchQuery'] ?? '';
        $this->tableSortColumn = $filters['tableSortColumn'] ?? $this->getSortColumnFromPresetView() ?? $this->getDefaultTableSortColumn();
        $this->tableSortDirection = $filters['tableSortDirection'] ?? $this->getSortDirectionFromPresetView() ?? $this->getDefaultTableSortDirection();
        $this->tableColumnSearches = $filters['tableColumnSearchQueries'] ?? [];
        $this->toggledTableColumns = $filters['toggledTableColumns'] ?? $this->getToggledColumnsFromPresetView() ?? $this->getDefaultTableColumnToggleState();
        $this->orderedToggledTableColumns = $this->getOrderedToggledTableColumnsFromUserView($filters);
        $this->tableGrouping = $filters['tableGrouping'] ?? $this->getGroupingFromPresetView() ?? $this->getDefaultTableGrouping();
        $this->tableGroupingDirection = $filters['tableGroupingDirection'] ?? $this->getGroupingDirectionFromPresetView() ?? null;

        parent::updatedToggledTableColumns();

        $this->applyToggledTableColumnsOrder();

        $this->getTableFiltersForm()->fill($this->tableFilters);

        $this->persistToSessionIfEnabled();
    }

    protected function getOrderedToggledTableColumnsFromUserView(array $filters): array
    {
        return $this->getOrderedToggledTableColumnsFromFilterArray($filters) ??
            $this->getToggledTableColumnsFromFilterArray($filters) ??
            $this->getOrderedToggledColumnsFromPresetView() ??
            $this->getDefaultToggledTableColumnsOrder();
    }

    protected function getOrderedToggledTableColumnsFromFilterArray(array $filters): ?array
    {
        $orderedToggledTableColumns = $filters['orderedToggledTableColumns'] ?? null;

        if (empty($orderedToggledTableColumns)) {
            return null;
        }

        if (is_array($orderedToggledTableColumns[array_key_first($orderedToggledTableColumns)])) {
            // TODO: With large amounts of columns and/or long column names, php will enforce it's own ordering algorithm for storing the array keys. This will cause the column order to be incorrect. I have since switched to storing the columns as an associative array to not lose the order of the columns. A migration will be provided in v4 to update the stored data.
            $visibleColumns = [];
            foreach ($orderedToggledTableColumns as $column) {
                $visibleColumns[$column['column']] = $column['isVisible'];
            }

            return $visibleColumns;
        }

        if (! is_bool($orderedToggledTableColumns[array_key_first($orderedToggledTableColumns)])) {
            // TODO: This is a temporary fix for the issue where the orderedToggledTableColumns array was not being saved correctly. A migration will be provided in v4 to fix this issue.
            return array_merge(
                array_map(fn () => true, $orderedToggledTableColumns),
                Arr::dot($this->toggledTableColumns),
            );
        }

        return $orderedToggledTableColumns;
    }

    protected function getToggledTableColumnsFromFilterArray(array $filters): ?array
    {
        $toggledTableColumns = $filters['toggledTableColumns'] ?? null;

        if (empty($toggledTableColumns)) {
            return null;
        }

        return Arr::dot($toggledTableColumns);
    }

    public function getFavoriteUserViews(): Collection
    {
        if (! Config::userViewsAreEnabled()) {
            return collect();
        }

        $columns = ['id', 'user_id', 'name', 'resource', 'is_public', 'is_global_favorite', 'status', 'filters', 'sort_order', 'color', 'icon'];

        if (Config::hasTenancy()) {
            $columns[] = Config::getTenantColumn();
        }

        return Config::getUserView()::query()
            ->select($columns)
            ->selectSub(function ($query) {
                $query->selectRaw('COUNT(' . DB::getTablePrefix() . Config::getUserTable() . '.' . Config::getUserTableKeyColumn() . ')')
                    ->from('filament_filter_set_user')
                    ->join(Config::getUserTable(), Config::getUserTable() . '.' . Config::getUserTableKeyColumn() . '', '=', 'filament_filter_set_user.user_id')
                    ->whereColumn('filament_filter_set_user.filter_set_id', 'filament_filter_sets.id')
                    ->where('filament_filter_set_user.user_id', Config::auth()->id());
            }, 'is_managed_by_current_user')
            ->selectSub(function ($query) {
                $query->select('managed_user_views.sort_order')
                    ->from('filament_filter_set_user as managed_user_views')
                    ->whereColumn('managed_user_views.filter_set_id', 'filament_filter_sets.id')
                    ->where('managed_user_views.user_id', Config::auth()->id())
                    ->limit(1);
            }, 'managed_by_current_user_sort_order')
            ->where('resource', $this->getResourceName())
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->local()->managedByCurrentUser();
                })
                    ->orWhere(function ($query) {
                        $query->global()->favoritedByCurrentUser();
                    })
                    ->orWhere(function ($query) {
                        $query->global()->doesntBelongToCurrentUser()->unManagedByCurrentUser()->meetsMinimumStatus();
                    });
            })
            ->orderBy('is_managed_by_current_user', Config::getNewGlobalUserViewSortPosition() === 'after' ? 'desc' : 'asc')
            ->orderBy('managed_by_current_user_sort_order', 'asc')
            ->limit(20)
            ->get();
    }

    public function getFavoriteUserViewsFromUserViews(): Collection
    {
        $userViews = $this->getUserViews();

        return $this->buildFavoriteUserViewsFrom($userViews);
    }

    public function getUserViewsArray(): array
    {
        $userViews = $this->getUserViews();

        return [
            'hiddenUserViews' => $this->buildUserViewsFrom($userViews),
            'favoriteUserViews' => $this->buildFavoriteUserViewsFrom($userViews),
            'globalUserViews' => $this->buildGlobalUserViewsFrom($userViews),
            'publicUserViews' => $this->buildPublicUserViewsFrom($userViews),
        ];
    }

    protected function getUserViews(): Collection
    {
        if (! Config::userViewsAreEnabled()) {
            return collect();
        }

        $columns = ['id', 'user_id', 'name', 'resource', 'is_public', 'is_global_favorite', 'status', 'filters', 'sort_order', 'color', 'icon'];

        if (Config::hasTenancy()) {
            $columns[] = Config::getTenantColumn();
        }

        return once(
            fn () => Config::getUserView()::query()
                ->select($columns)
                ->selectSub(function ($query) {
                    $query->selectRaw('COUNT(' . DB::getTablePrefix() . Config::getUserTable() . '.' . Config::getUserTableKeyColumn() . ')')
                        ->from('filament_filter_set_user')
                        ->join(Config::getUserTable(), Config::getUserTable() . '.' . Config::getUserTableKeyColumn() . '', '=', 'filament_filter_set_user.user_id')
                        ->whereColumn('filament_filter_set_user.filter_set_id', 'filament_filter_sets.id')
                        ->where('filament_filter_set_user.user_id', Config::auth()->id());
                }, 'is_managed_by_current_user')
                ->selectSub(function ($query) {
                    $query->select('managed_user_views.id')
                        ->from('filament_filter_set_user as managed_user_views')
                        ->whereColumn('managed_user_views.filter_set_id', 'filament_filter_sets.id')
                        ->where('managed_user_views.user_id', Config::auth()->id())
                        ->limit(1);
                }, 'managed_by_current_user_id')
                ->selectSub(function ($query) {
                    $query->select('managed_user_views.sort_order')
                        ->from('filament_filter_set_user as managed_user_views')
                        ->whereColumn('managed_user_views.filter_set_id', 'filament_filter_sets.id')
                        ->where('managed_user_views.user_id', Config::auth()->id())
                        ->limit(1);
                }, 'managed_by_current_user_sort_order')
                ->selectSub(function ($query) {
                    $query->select('managed_user_views.is_visible')
                        ->from('filament_filter_set_user as managed_user_views')
                        ->whereColumn('managed_user_views.filter_set_id', 'filament_filter_sets.id')
                        ->where('managed_user_views.user_id', Config::auth()->id())
                        ->limit(1);
                }, 'managed_by_current_user_is_visible')
                ->where('resource', $this->getResourceName())
                ->where(function ($query) {
                    $query->managedByCurrentUser()
                        ->orWhere(function ($query) {
                            $query->global()->meetsMinimumStatus();
                        })
                        ->orWhere(function ($query) {
                            $query->public()->meetsMinimumStatus();
                        })
                        ->orWhere('user_id', Config::auth()?->id());
                })
                ->get()
        );
    }

    protected function buildFavoriteUserViewsFrom(Collection $userViews): Collection
    {
        return $userViews
            ->filter(function ($view) {
                return
                    ($view->isLocal() && $view->is_managed_by_current_user) ||
                    ($view->isGlobal() && $view->managed_by_current_user_is_visible) ||
                    ($view->isGlobal() && $view->doesntBelongToCurrentUser() && ! $view->is_managed_by_current_user);
            })
            ->sortBy([
                ['is_managed_by_current_user', Config::getNewGlobalUserViewSortPosition() === 'after' ? 'desc' : 'asc'],
                ['managed_by_current_user_sort_order'],
            ])
            ->values();
    }

    protected function buildUserViewsFrom(Collection $userViews): Collection
    {
        return $userViews
            ->where('user_id', Config::auth()->id())
            ->reject(function ($view) {
                return
                    ($view->is_managed_by_current_user && $view->managed_by_current_user_is_visible) ||
                    ($view->isGlobal() && $view->belongsToCurrentUser() && $view->managed_by_current_user_is_visible);
            })
            ->sortBy('sort_order')
            ->values();
    }

    protected function buildGlobalUserViewsFrom(Collection $userViews): Collection
    {
        return $userViews
            ->where('user_id', '!=', Config::auth()->id())
            ->where('is_global_favorite', true)
            ->where('is_managed_by_current_user', true)
            ->where('managed_by_current_user_is_visible', false)
            ->sortBy('name')
            ->values();
    }

    protected function buildPublicUserViewsFrom(Collection $userViews): Collection
    {
        return $userViews
            ->where('user_id', '!=', Config::auth()->id())
            ->where('is_managed_by_current_user', false)
            ->where('is_public', true)
            ->where('is_global_favorite', false)
            ->sortBy('name')
            ->values();
    }

    protected function mergeTableFilters(array $filters): ?array
    {
        if (! is_array($this->tableFilters)) {
            return null;
        }

        if (
            Arr::exists($filters, 'advanced_filter_builder') ||
            ! Arr::exists($this->tableFilters, 'advanced_filter_builder')
        ) {
            return array_merge($this->tableFilters, $filters);
        }

        foreach ($filters as $name => $filter) {
            if (Arr::exists($this->tableFilters, $name)) {
                $this->tableFilters[$name] = $filter;

                continue;
            }

            $filterGroupKey = array_key_first($this->tableFilters['advanced_filter_builder']['or_group']);

            $filterGroupFilters = data_get($this->tableFilters, "advanced_filter_builder.or_group.{$filterGroupKey}.data.and_group");

            foreach ($filterGroupFilters ?? [] as $key => $filterGroupFilter) {
                if ($filterGroupFilter['type'] === $name) {
                    data_set($this->tableFilters, "advanced_filter_builder.or_group.{$filterGroupKey}.data.and_group.{$key}.data", $filter);

                    break;
                }
            }
        }

        return $this->tableFilters;
    }
}
