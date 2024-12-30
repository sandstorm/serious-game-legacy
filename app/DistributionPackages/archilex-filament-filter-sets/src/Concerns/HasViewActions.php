<?php

namespace Archilex\AdvancedTables\Concerns;

use Archilex\AdvancedTables\Support\Authorize;
use Archilex\AdvancedTables\Support\Config;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

trait HasViewActions
{
    public function addViewToFavoritesAction(): Action
    {
        return Action::make('addViewToFavorites')
            ->label(__('advanced-tables::advanced-tables.view_manager.actions.add_view_to_favorites'))
            ->extraAttributes(['class' => 'advanced-tables-add-view-to-favorites'])
            ->icon('heroicon-o-star')
            ->action(function (array $arguments) {
                if (Arr::get($arguments, 'presetView', 0)) {
                    $existingPresetView = [
                        'name' => $arguments['presetView'],
                        'resource' => $this->getResourceName(),
                    ];

                    if (Config::hasTenancy()) {
                        $existingPresetView[Config::getTenantColumn()] = Config::getTenantId();
                    }

                    return Config::auth()->user()
                        ->managedPresetViews()
                        ->updateOrCreate(
                            $existingPresetView,
                            ['is_favorite' => true]
                        );
                }

                return Config::auth()->user()
                    ->managedUserViews()
                    ->syncWithPivotValues($arguments['userView'], [
                        'is_visible' => true,
                        'sort_order' => Config::getUserView()::query()->favoritedByCurrentUser()->count() + 1,
                    ], false);
            });
    }

    public function applyViewAction(): Action
    {
        return Action::make('applyView')
            ->label(__('advanced-tables::advanced-tables.view_manager.actions.apply_view'))
            ->extraAttributes(['class' => 'advanced-tables-apply-view'])
            ->icon('heroicon-s-arrow-small-right')
            ->action(
                fn (array $arguments) => $arguments['presetView'] ?? null
                    ? $this->loadPresetView($arguments['presetView'])
                    : $this->loadUserView($arguments['userView'], $arguments['filters'])
            );
    }

    public function saveUserViewAction(): Action
    {
        return Action::make('saveUserView')
            ->label(fn () => Config::isQuickSaveInFavoritesBar() ? __('advanced-tables::advanced-tables.view_manager.actions.save_view') : __('advanced-tables::advanced-tables.view_manager.actions.save'))
            ->extraAttributes(['class' => 'advanced-tables-save-view'])
            ->view(Config::isQuickSaveInFavoritesBar() || Config::isQuickSaveInTable() ? 'filament-actions::icon-button-action' : 'filament-actions::link-action')
            ->icon(fn () => Config::isQuickSaveInFavoritesBar() || Config::isQuickSaveInTable() ? Config::getQuickSaveIcon() : null)
            ->iconSize(fn () => Config::isQuickSaveInFavoritesBar() || Config::isQuickSaveInTable() ? 'md' : null)
            ->color(Config::isQuickSaveInTable() ? 'gray' : 'primary')
            ->form(fn () => $this->getSaveOptionFormSchema())
            ->slideOver(fn () => Config::showQuickSaveAsSlideOver())
            ->modalHeading(__('advanced-tables::advanced-tables.quick_save.save.modal_heading'))
            ->modalSubmitActionLabel(__('advanced-tables::advanced-tables.quick_save.save.submit_label'))
            ->modalWidth(fn () => Config::showQuickSaveAsSlideOver() ? 'md' : '4xl')
            ->visible(Authorize::canPerformAction('create'))
            ->action(function (array $data) {
                $view = $this->saveUserView($data);

                Notification::make()
                    ->success()
                    ->title(__('advanced-tables::advanced-tables.notifications.save_view.saved.title'))
                    ->send();

                $this->defaultViewIsActive = false;
                $this->activeUserView = $view?->id;
            })
            ->afterFormFilled(function (Action $action) {
                if (filled($this->activePresetView) && ! Config::canCreateUsingPresetView()) {
                    Notification::make()
                        ->warning()
                        ->title(__('advanced-tables::advanced-tables.notifications.preset_views.title'))
                        ->body(__('advanced-tables::advanced-tables.notifications.preset_views.body'))
                        ->persistent()
                        ->send();

                    $action->cancel();
                }
            });
    }

    public function deleteViewAction(): Action
    {
        return Action::make('deleteView')
            ->label(__('advanced-tables::advanced-tables.view_manager.actions.delete_view'))
            ->extraAttributes(['class' => 'advanced-tables-delete-view'])
            ->icon('heroicon-s-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalDescription(function (array $arguments) {
                // Currently throwing Error: Undefined array key "type"
                // due to multiple LW requests? Will investigate later.

                // return in_array($arguments['type'], ['public', 'global'])
                //     ? __('advanced-tables::advanced-tables.view_manager.actions.delete_view_description', ['type' => $arguments['type']])
                //     : __('filament-actions::modal.confirmation');

                return __('filament-actions::modal.confirmation');
            })
            ->modalSubmitActionLabel(__('advanced-tables::advanced-tables.view_manager.actions.delete_view_modal_submit_label'))
            ->action(function (array $arguments) {
                $view = Config::getUserView()::find($arguments['userView']);

                $view->userManagedUserViews()->detach(Config::auth()->id());

                $view->delete();

                Notification::make()
                    ->success()
                    ->title(__('filament-actions::delete.single.notifications.deleted.title'))
                    ->send();
            });
    }

    public function editViewAction(): Action
    {
        return Action::make('editView')
            ->label(__('advanced-tables::advanced-tables.view_manager.actions.edit_view'))
            ->extraAttributes(['class' => 'advanced-tables-edit-view'])
            ->icon('heroicon-s-pencil-square')
            ->slideOver(fn () => Config::showQuickSaveAsSlideOver())
            ->modalWidth(fn () => Config::showQuickSaveAsSlideOver() ? 'md' : '4xl')
            ->modalSubmitActionLabel(__('filament-actions::edit.single.modal.actions.save.label'))
            ->form(fn () => $this->getSaveOptionFormSchema())
            ->fillForm(function (array $arguments) {
                return [
                    ...(Config::getUserView()::find($arguments['userView'])->attributesToArray()),
                    ...(['is_managed_by_current_user' => $arguments['isFavorite']]),
                ];
            })
            ->action(function (array $arguments, array $data) {
                $view = Config::getUserView()::find($arguments['userView']);

                if ($data['is_managed_by_current_user']) {
                    $view->userManagedUserViews()->syncWithoutDetaching(Config::auth()->id());
                } else {
                    $view->userManagedUserViews()->detach(Config::auth()->id());
                }

                $view->update(Arr::except($data, ['is_managed_by_current_user']));

                Notification::make()
                    ->success()
                    ->title(__('advanced-tables::advanced-tables.notifications.edit_view.saved.title'))
                    ->send();
            });
    }

    public function replaceViewAction(): Action
    {
        return Action::make('replaceView')
            ->label(__('advanced-tables::advanced-tables.view_manager.actions.replace_view'))
            ->extraAttributes(['class' => 'advanced-tables-replace-view'])
            ->icon('heroicon-s-arrows-right-left')
            ->color('danger')
            ->requiresConfirmation()
            ->modalDescription(__('advanced-tables::advanced-tables.view_manager.actions.replace_view_modal_description'))
            ->modalSubmitActionLabel(__('advanced-tables::advanced-tables.view_manager.actions.replace_view_modal_submit_label'))
            ->action(function (array $arguments) {

                $view = Config::getUserView()::findOrFail($arguments['userView']);

                $view->update([
                    'filters' => $this->getFilters(),
                    'indicators' => $this->getMergedFilterIndicators(),
                ]);

                Notification::make()
                    ->success()
                    ->title(__('advanced-tables::advanced-tables.notifications.replace_view.replaced.title'))
                    ->send();

                $this->defaultViewIsActive = false;
                $this->activeUserView = $view?->id;
            });
    }

    public function removeViewFromFavoritesAction(): Action
    {
        return Action::make('removeViewFromFavorites')
            ->label(__('advanced-tables::advanced-tables.view_manager.actions.remove_view_from_favorites'))
            ->extraAttributes(['class' => 'advanced-tables-remove-view-from-favorites'])
            ->icon('heroicon-o-minus-circle')
            ->action(function (array $arguments) {
                if (Arr::get($arguments, 'presetView', 0)) {
                    $existingPresetView = [
                        'name' => $arguments['presetView'],
                        'resource' => $this->getResourceName(),
                    ];

                    if (Config::hasTenancy()) {
                        $existingPresetView[Config::getTenantColumn()] = Config::getTenantId();
                    }

                    return Config::auth()->user()
                        ->managedPresetViews()
                        ->updateOrCreate(
                            $existingPresetView,
                            ['is_favorite' => false]
                        );
                }

                if (Arr::get($arguments, 'shouldHide', 0)) {
                    return Config::auth()->user()
                        ->managedUserViews()
                        ->syncWithPivotValues($arguments['userView'], [
                            'is_visible' => false,
                        ], false);
                }

                return Config::auth()->user()
                    ->managedUserViews()
                    ->detach($arguments['userView']);
            });
    }

    public function showViewManagerAction(): Action
    {
        return Action::make('showViewManager')
            ->label(__('advanced-tables::advanced-tables.view_manager.actions.show_view_manager'))
            ->extraAttributes(['class' => 'advanced-tables-show-view-manager'])
            ->modalHeading(__('advanced-tables::advanced-tables.view_manager.heading'))
            ->iconButton()
            ->icon(Config::getViewManagerIcon())
            ->iconSize('md')
            ->color(Config::isViewManagerInTable() ? 'gray' : 'primary')
            ->slideOver()
            ->modalWidth('md')
            ->modalCancelAction(false)
            ->modalSubmitAction(false)
            ->registerModalActions([
                $this->applyViewAction(),
                $this->deleteViewAction(),
                $this->editViewAction(),
                $this->replaceViewAction(),
                $this->addViewToFavoritesAction(),
                $this->removeViewFromFavoritesAction(),
            ])
            ->modalContent(view('advanced-tables::components.view-manager.index'));
    }

    public function getPresetViewActions(string $viewKey, bool $isFavorite, bool $canBeManaged): array
    {
        $actions = [];

        if (Config::hasApplyButtonInViewManager()) {
            $actions[] = ($this->applyViewAction)(['presetView' => $viewKey]);
        }

        if ($isFavorite && $canBeManaged) {
            $actions[] = ($this->removeViewFromFavoritesAction)(['presetView' => $viewKey]);
        }

        if (! $isFavorite && $canBeManaged) {
            $actions[] = ($this->addViewToFavoritesAction)(['presetView' => $viewKey]);
        }

        return $actions;
    }

    public function getUserViewActions(string $viewKey, bool $isFavorite, bool $canBeManaged, bool $belongsToCurrentUser, array $filters, ?string $visibility = null): array
    {
        $actions = [];

        if (Config::hasApplyButtonInViewManager()) {
            $actions[] = ($this->applyViewAction)(['userView' => $viewKey, 'filters' => $filters]);
        }

        if ($isFavorite && $canBeManaged) {
            $actions[] = ($this->removeViewFromFavoritesAction)(['userView' => $viewKey, 'shouldHide' => (! $belongsToCurrentUser && $visibility === 'global')]);
        }

        if (! $isFavorite && $canBeManaged) {
            $actions[] = ($this->addViewToFavoritesAction)(['userView' => $viewKey]);
        }

        if ($belongsToCurrentUser) {
            $actions[] = ($this->editViewAction)(['userView' => $viewKey, 'isFavorite' => $isFavorite]);
        }

        if ($belongsToCurrentUser) {
            return [
                \Filament\Actions\ActionGroup::make(
                    $actions
                )->dropdown(false),
                ($this->replaceViewAction)([
                    'userView' => $viewKey,
                    'type' => $visibility,
                ]),
                ($this->deleteViewAction)([
                    'userView' => $viewKey,
                    'type' => $visibility,
                ]),
            ];
        }

        return $actions;
    }

    protected function getActivePresetViewIndicators(): ?array
    {
        $presetView = $this->getActivePresetView();

        if (! $presetView || ! $presetView->modifiesQuery()) {
            return null;
        }

        return collect(__('advanced-tables::advanced-tables.forms.preset_view.label') . ': ' . $this->getActivePresetViewLabel())
            ->when(
                $presetView->getIndicator(),
                fn ($collection, $indicators) => $collection->merge(__('advanced-tables::advanced-tables.forms.preset_view.query_label') . ': ' . $indicators)
            )
            ->toArray();
    }

    protected function getFilterIndicators(): array
    {
        return collect($this->getTable()->getFilters())
            ->mapWithKeys(function (\Filament\Tables\Filters\BaseFilter $filter): array {
                $indicators = [];

                foreach ($filter->getIndicators() as $indicator) {
                    $indicators[] = $indicator->getLabel();
                }

                return [$filter->getName() => $indicators];
            })
            ->filter(fn (array $indicators): bool => count($indicators))
            ->flatten()
            ->toArray();
    }

    protected function getTableColumnSearchQueryIndicators(): array
    {
        return collect($this->getTableColumnSearches())
            ->map(fn ($searchQuery, $column) => $this->getTable()->getColumns()[$column]->getLabel() . ': ' . $searchQuery)
            ->values()
            ->toArray();
    }

    protected function getTableGroupingIndicator(): ?string
    {
        return filled($this->tableGrouping) && $this->tableGrouping !== 'null'
            ? __('filament-tables::table.grouping.fields.group.label') . ': ' . $this->getTable()->getGrouping()->getLabel()
            : null;
    }

    protected function getTableGroupingDirectionIndicator(): ?string
    {
        return filled($this->tableGroupingDirection) && $this->tableGrouping !== 'null'
            ? __('filament-tables::table.grouping.fields.direction.label') . ': ' . __('filament-tables::table.grouping.fields.direction.options.' . $this->tableGroupingDirection)
            : null;
    }

    protected function getTableSortColumnIndicator(): ?string
    {
        return filled($this->tableSortColumn) && $this->tableSortColumn !== 'null' && $this->isValidTableColumn($this->tableSortColumn)
            ? __('filament-tables::table.sorting.fields.column.label') . ': ' . $this->getTable()->getColumns()[$this->tableSortColumn]->getLabel()
            : null;
    }

    protected function getTableSortColumnDirectionIndicator(): ?string
    {
        return filled($this->tableSortDirection) && $this->tableSortDirection !== 'null' && $this->isValidTableColumn($this->tableSortColumn)
            ? __('filament-tables::table.sorting.fields.direction.label') . ': ' . __('filament-tables::table.sorting.fields.direction.options.' . $this->tableSortDirection)
            : null;
    }

    protected function getMergedFilterIndicators(): array
    {
        return collect()
            ->when(
                $this->getActivePresetViewIndicators(),
                fn ($collection, $indicators) => $collection->merge($indicators)
            )
            ->merge($this->getFilterIndicators())
            ->when(
                $this->getTableColumnSearchQueryIndicators(),
                fn ($collection, $indicators) => $collection->merge($indicators)
            )
            ->when(
                $this->getTableGroupingIndicator(),
                fn ($collection, $indicator) => $collection->merge($indicator)
            )
            ->when(
                $this->getTableGroupingDirectionIndicator(),
                fn ($collection, $indicator) => $collection->merge($indicator)
            )
            ->when(
                $this->tableSearch,
                fn ($collection, $searchQuery) => $collection->merge([__('filament-tables::table.fields.search.label') . ': ' . $searchQuery])
            )
            ->when(
                $this->getTableSortColumnIndicator(),
                fn ($collection, $indicator) => $collection->merge($indicator)
            )
            ->when(
                $this->getTableSortColumnDirectionIndicator(),
                fn ($collection, $indicator) => $collection->merge($indicator)
            )
            ->toArray();
    }

    protected function saveUserView(array $data): Model
    {
        $existingView = ['name' => $data['name'], 'resource' => $this->getResourceName(), 'user_id' => Config::auth()?->id()];

        if (Config::hasTenancy()) {
            $existingView[Config::getTenantColumn()] = Config::getTenantId();
        }

        $view = Config::getUserView()::updateOrCreate($existingView, [
            'filters' => $this->getFilters(),
            'indicators' => $this->getMergedFilterIndicators(),
            'is_public' => $data['is_public'] ?? false,
            'is_global_favorite' => $data['is_global_favorite'] ?? false,
            'icon' => $data['icon'] ?? null,
            'color' => $data['color'] ?? null,
            'status' => Config::getInitialStatus(),
        ]);

        if ($data['is_managed_by_current_user']) {
            $view->userManagedUserViews()
                ->syncWithPivotValues(Config::auth()->id(), [
                    'sort_order' => Config::getUserView()::query()->favoritedByCurrentUser()->count() + 1,
                ], false);
        } else {
            $view->userManagedUserViews()->detach(Config::auth()->id());
        }

        return $view;
    }

    protected function getFilters(): array
    {
        return collect()
            ->when(
                $this->getActivePresetView()?->modifiesQuery(),
                fn ($collection) => $collection->merge(['activeSet' => $this->activePresetView])
            )
            ->when(
                $this->tableFilters,
                fn ($collection, $filters) => $collection->merge(['tableFilters' => collect($filters)->forget('userView')->filter(fn (?array $filter): bool => collect($filter)?->contains(fn ($value) => ! is_null($value)))])
            )
            ->when(
                $this->tableColumnSearches,
                fn ($collection, $searchQueries) => $collection->merge(['tableColumnSearchQueries' => $searchQueries])
            )
            ->when(
                $this->tableGrouping,
                fn ($collection, $grouping) => $collection->merge(['tableGrouping' => $grouping])
            )
            ->when(
                $this->tableGroupingDirection,
                fn ($collection, $groupingDirection) => $collection->merge(['tableGroupingDirection' => $groupingDirection])
            )
            ->when(
                $this->tableSearch,
                fn ($collection, $searchQuery) => $collection->merge(['tableSearchQuery' => $searchQuery])
            )
            ->when(
                $this->tableSortColumn,
                fn ($collection, $sortColumn) => $collection->merge(['tableSortColumn' => $sortColumn])
            )
            ->when(
                $this->tableSortDirection,
                fn ($collection, $sortDirection) => $collection->merge(['tableSortDirection' => $sortDirection])
            )
            ->when(
                $this->toggledTableColumns,
                fn ($collection, $columns) => $collection->merge(['toggledTableColumns' => $columns])
            )
            ->when(
                $this->orderedToggledTableColumns,
                fn ($collection, $columns) => $collection->merge(['orderedToggledTableColumns' => $this->buildOrderedToggledTableColumns($columns)])
            )
            ->toArray();
    }

    protected function getActivePresetViewLabel(): ?string
    {
        $presetView = $this->getActivePresetView();

        if (! $presetView) {
            return null;
        }

        return $presetView->getLabel() ?? $this->generatePresetViewLabel($this->activePresetView);
    }

    protected function buildOrderedToggledTableColumns(array $columns): array
    {
        return collect($columns)
            ->map(function ($isVisible, $column) {
                return [
                    'column' => $column,
                    'isVisible' => $isVisible,
                ];
            })
            ->values()
            ->toArray();
    }

    protected function isValidTableColumn(string $column): bool
    {
        return isset($this->getTable()->getColumns()[$column]);
    }
}
