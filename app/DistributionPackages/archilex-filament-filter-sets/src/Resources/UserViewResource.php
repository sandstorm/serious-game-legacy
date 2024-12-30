<?php

namespace Archilex\AdvancedTables\Resources;

use Archilex\AdvancedTables\Enums\Status;
use Archilex\AdvancedTables\Filters\UserSelectFilter;
use Archilex\AdvancedTables\Models\UserView;
use Archilex\AdvancedTables\Resources\UserViewResource\Pages;
use Archilex\AdvancedTables\Resources\UserViewResource\Pages\ManageUserViews;
use Archilex\AdvancedTables\Support\Authorize;
use Archilex\AdvancedTables\Support\Config;
use Archilex\ToggleIconColumn\Columns\ToggleIconColumn;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class UserViewResource extends Resource
{
    protected static ?string $tenantOwnershipRelationshipName = 'tenant';

    public static function getModel(): string
    {
        return Config::getUserView();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = static::getModel()::query();

        if (! Config::hasTenancy()) {
            return $query;
        }

        if (
            static::isScopedToTenant() &&
            ($tenant = Filament::getTenant())
        ) {
            static::scopeEloquentQueryToTenant($query, $tenant);
        }

        return $query;
    }

    public static function getModelLabel(): string
    {
        return __('advanced-tables::advanced-tables.user_view_resource.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('advanced-tables::advanced-tables.user_view_resource.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('advanced-tables::advanced-tables.user_view_resource.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return Config::getResourceNavigationGroup();
    }

    public static function getNavigationSort(): ?int
    {
        return Config::getResourceNavigationSort();
    }

    public static function getNavigationIcon(): ?string
    {
        return Config::getResourceNavigationIcon();
    }

    public static function getNavigationBadge(): ?string
    {
        if (! Config::hasResourceNavigationBadge()) {
            return null;
        }

        if (in_array(Config::getMinimumStatusForDisplay(), ['approved', Status::Approved])) {
            return static::getModel()::query()
                ->pending()
                ->resourcePanels()
                ->count();
        }

        return null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(ManageUserViews::getUserViewResourceFormSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->resourcePanels())
            ->columns([
                TextColumn::make('name')
                    ->label(__('advanced-tables::advanced-tables.tables.columns.name'))
                    ->description(fn (Model $record) => $record->resource_name)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('panel')
                    ->label(__('advanced-tables::advanced-tables.tables.columns.panel'))
                    ->state(function (Model $record) {
                        $panels = filament()->getPanels();

                        foreach ($panels as $name => $panel) {
                            if (in_array($record->resource, Config::getUserView()::getPanelResources($name))) {
                                return Str::title($name);
                            }
                        }

                        return null;
                    })
                    ->visible(fn () => count(filament()->getPanels()) > 1)
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('icon')
                    ->label(__('advanced-tables::advanced-tables.tables.columns.icon'))
                    ->icons(fn (Model $record) => $record->icon ? [$record->icon => $record->icon] : [])
                    ->size('md')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label(__('advanced-tables::advanced-tables.tables.columns.status'))
                    ->badge()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('user.' . Config::getUserTableNameColumn())
                    ->label(__('advanced-tables::advanced-tables.tables.columns.user'))
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('resource')
                    ->label(__('advanced-tables::advanced-tables.tables.columns.resource'))
                    ->searchable(['resource'])
                    ->sortable(['resource'])
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('indicators')
                    ->badge()
                    ->label(__('advanced-tables::advanced-tables.tables.columns.filters'))
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleIconColumn::make('is_public')
                    ->label(__('advanced-tables::advanced-tables.tables.columns.is_public'))
                    ->size('md')
                    ->onIcon('heroicon-s-eye')
                    ->offIcon('heroicon-o-eye-slash')
                    ->onColor('primary')
                    ->offColor('secondary')
                    ->disabled(fn (Model $record) => ! Authorize::canPerformAction('update', $record))
                    ->alignCenter()
                    ->tooltip(fn (Model $record) => Authorize::canPerformAction('update', $record) ? ($record->is_public ? __('advanced-tables::advanced-tables.tables.tooltips.is_public.make_private') : __('advanced-tables::advanced-tables.tables.tooltips.is_public.make_public')) : false)
                    ->sortable(),
                ToggleIconColumn::make('is_global_favorite')
                    ->label(__('advanced-tables::advanced-tables.tables.columns.is_global_favorite'))
                    ->size('md')
                    ->onIcon('heroicon-s-globe-alt')
                    ->offIcon('heroicon-o-globe-alt')
                    ->onColor('primary')
                    ->offColor('secondary')
                    ->disabled(fn (Model $record) => ! Authorize::canPerformAction('update', $record))
                    ->alignCenter()
                    ->tooltip(fn (Model $record) => Authorize::canPerformAction('update', $record) ? ($record->is_global_favorite ? __('advanced-tables::advanced-tables.tables.tooltips.is_global_favorite.make_personal') : __('advanced-tables::advanced-tables.tables.tooltips.is_global_favorite.make_global')) : false)
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->groups([
                'user.' . Config::getUserTableNameColumn(),
                Group::make('resource')
                    ->getTitleFromRecordUsing(fn (UserView $record): string => $record->resource_name),
            ])
            ->filters([
                UserSelectFilter::make('user_id')
                    ->label(__('advanced-tables::advanced-tables.forms.user'))
                    ->relationship('user', Config::getUserTableNameColumn())
                    ->multiple(),
                SelectFilter::make('status')
                    ->label(__('advanced-tables::advanced-tables.forms.status.label'))
                    ->multiple()
                    ->options(Status::class),
                Filter::make('panels')
                    ->label(__('advanced-tables::advanced-tables.forms.panels.label'))
                    ->form([
                        Select::make('panels')
                            ->multiple()
                            ->options(
                                fn () => collect(filament()->getPanels())
                                    ->mapWithKeys(
                                        fn ($value, $key) => [$key => Str::title($key)]
                                    )
                                    ->toArray()
                            ),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['panels'],
                                fn (Builder $query, $panels): Builder => $query->whereIn('resource', Config::getUserView()::getPanelsResources($panels))
                            );
                    })
                    ->indicateUsing(function (array $data) {
                        if (blank($data['panels'] ?? null)) {
                            return [];
                        }

                        $labels = collect($data['panels'])->join(', ', ' & ');

                        return [__('advanced-tables::advanced-tables.forms.panels.label') . ': ' . $labels];
                    })
                    ->visible(fn () => count(filament()->getPanels()) > 1),
                SelectFilter::make('resource')
                    ->label(__('advanced-tables::advanced-tables.forms.resource'))
                    ->options(
                        fn () => Config::getUserView()::query()
                            ->resourcePanels()
                            ->distinct()
                            ->pluck('resource', 'resource')
                            ->when(filled($panels = Config::getResourcePanels()) && count($panels) > 1, function (Collection $collection) use ($panels) {
                                return $collection->groupBy(function ($view, $key) use ($panels) {
                                    foreach ($panels as $panel) {
                                        if (in_array($view, Config::getUserView()::getPanelResources($panel))) {
                                            return Str::title($panel);
                                        }
                                    }
                                }, preserveKeys: true)
                                    ->map(fn (Collection $collection) => static::generateOptionsArray($collection));
                            }, fn (Collection $collection) => static::generateOptionsArray($collection))
                    )
                    ->indicateUsing(function (array $data) {
                        if (! $data['value']) {
                            return null;
                        }

                        if (static::isRelationManager($data['value'])) {
                            return __('advanced-tables::advanced-tables.forms.resource') . ': ' . static::getRelationManagerResourceName($data['value']);
                        }

                        if (static::isTableWidget($data['value'])) {
                            return __('advanced-tables::advanced-tables.forms.resource') . ': ' . static::getTableWidgetName($data['value']);
                        }

                        if (static::isManageRelatedRecords($data['value'])) {
                            return __('advanced-tables::advanced-tables.forms.resource') . ': ' . static::getManageRelatedRecordsName($data['value']);
                        }

                        return __('advanced-tables::advanced-tables.forms.resource') . ': ' . Str::of(Str::replace('Archilex\FilamentFilterSets', 'Archilex\AdvancedTables', $data['value'])::getPluralModelLabel())->ucfirst();
                    }),
                TernaryFilter::make('is_public')
                    ->label(__('advanced-tables::advanced-tables.forms.public.toggle_label')),
                TernaryFilter::make('is_global_favorite')
                    ->label(__('advanced-tables::advanced-tables.forms.global_favorite.toggle_label')),
            ])
            ->actions([
                ActionGroup::make([
                    ActionGroup::make([
                        EditAction::make()
                            ->slideOver(fn () => Config::showQuickSaveAsSlideOver())
                            ->modalWidth(fn () => Config::showQuickSaveAsSlideOver() ? 'md' : '4xl'),
                        Action::make('open')
                            ->label(__('advanced-tables::advanced-tables.tables.actions.buttons.open'))
                            ->icon('heroicon-s-link')
                            ->visible(function (Model $record): bool {
                                return in_array($record->resource, Config::getUserView()::getPanelResources()) &&
                                    method_exists($record->resource, 'getUrl') &&
                                    ! (app($record->resource) instanceof ManageRelatedRecords);
                            })
                            ->url(fn (Model $record): string => in_array($record->resource, Config::getUserView()::getPanelResources()) ? $record->resource::getUrl('index', [$record->getQueryString()]) : ''),
                        Action::make('approve')
                            ->label(__('advanced-tables::advanced-tables.tables.actions.buttons.approve'))
                            ->icon('heroicon-s-check-badge')
                            ->authorize(fn (Model $record) => Gate::allows('approve', $record))
                            ->visible(fn (Model $record): bool => $record->status !== Status::Approved)
                            ->action(fn (Model $record) => $record->update(['status' => 'approved'])),
                    ])
                        ->dropdown(false),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUserViews::route('/'),
        ];
    }

    protected static function isRelationManager($class): bool
    {
        return is_subclass_of($class, RelationManager::class);
    }

    protected static function isTableWidget($class): bool
    {
        return is_subclass_of($class, TableWidget::class);
    }

    protected static function isManageRelatedRecords($class): bool
    {
        return is_subclass_of($class, ManageRelatedRecords::class);
    }

    protected static function generateOptionsArray(Collection $collection): array
    {
        return $collection->mapWithKeys(function ($value, $key) {
            if (static::isRelationManager($value)) {
                return [$key => static::getRelationManagerResourceName($value)];
            }

            if (static::isTableWidget($value)) {
                return [$key => static::getTableWidgetName($value)];
            }

            if (static::isManageRelatedRecords($value)) {
                return [$key => static::getManageRelatedRecordsName($value)];
            }

            return [$key => Str::of(Str::replace('Archilex\FilamentFilterSets', 'Archilex\AdvancedTables', $value)::getPluralModelLabel())->ucfirst()->toString()];
        })
            ->toArray();
    }

    protected static function getRelationManagerResourceName($class): string
    {
        $resource = Str::of($class)
            ->beforeLast('\\RelationManagers\\')
            ->toString()::getPluralModelLabel();

        $relationManager = Str::of($class)
            ->afterLast('\\RelationManagers\\')
            ->beforeLast('RelationManager')
            ->headline()
            ->toString();

        return Str::title($resource . ' > ' . $relationManager);
    }

    protected static function getTableWidgetName($class): string
    {
        $location = Str::of($class)
            ->beforeLast('\\Widgets\\')
            ->toString();

        $location = $location === 'App\Filament'
            ? 'Dashboard'
            : $location::getPluralModelLabel();

        $widget = Str::of($class)
            ->afterLast('\\Widgets\\')
            ->headline()
            ->toString();

        return Str::title($location . ' > ' . $widget);
    }

    protected static function getManageRelatedRecordsName($class): string
    {
        $location = Str::of($class)
            ->beforeLast('\\Pages\\')
            ->toString();

        $location = $location === 'App\Filament'
            ? 'Dashboard'
            : $location::getPluralModelLabel();

        $page = Str::of($class)
            ->afterLast('\\Pages\\')
            ->headline()
            ->toString();

        return Str::title($location . ' > ' . $page);
    }
}
