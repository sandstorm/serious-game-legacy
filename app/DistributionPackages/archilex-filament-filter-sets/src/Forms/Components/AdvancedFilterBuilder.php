<?php

namespace Archilex\AdvancedTables\Forms\Components;

use Archilex\AdvancedTables\Filters\Concerns\HasFiltersLayout;
use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Support\Str;
use Livewire\Component;

class AdvancedFilterBuilder extends Builder
{
    use HasFiltersLayout;

    protected $filters = [];

    protected string | Closure | null $blockPickerMaxHeight = null;

    protected bool | Closure $blockPickerHasSearch = false;

    protected bool $hasOrGroups = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(static function (Builder $component, ?array $state): void {
            $items = [];

            foreach ($state ?? [] as $itemData) {
                $items[(string) Str::random(3)] = $itemData;
            }

            $component->state($items);
        });

        $this->registerActions([
            fn (Builder $component): Action => $component->getAddAction(),
            fn (Builder $component): Action => $component->getDeleteAction(),
            fn (Builder $component): Action => $component->getDeleteIconAction(),
            fn (Builder $component): Action => $component->getExpandViewAction(),
        ]);
    }

    public function getView(): string
    {
        return 'advanced-tables::forms.components.advanced-filter-builder';
    }

    public function getAddAction(): Action
    {
        return Action::make('add')
            ->label(fn (Builder $component) => $component->getAddActionLabel())
            ->color('gray')
            ->action(function (array $arguments, Builder $component): void {
                $items = $component->getState();

                $newUuid = (string) Str::random(3);

                $items[$newUuid] = [
                    'type' => $arguments['block'],
                    'data' => [],
                ];

                $component->state($items);

                $component->getChildComponentContainers()[$newUuid]->fill();

                $tableFilters = $component->getLivewire()->getTable()->hasDeferredFilters()
                    ? 'tableDeferredFilters'
                    : 'tableFilters';

                $parentUuid = collect($component->getLivewire()->{$tableFilters}['advanced_filter_builder']['or_group'])
                    ->filter(function ($orGroup) use ($newUuid) {
                        return
                            array_key_exists('data', $orGroup) &&
                            array_key_exists('and_group', $orGroup['data']) &&
                            collect($orGroup['data']['and_group'])->has($newUuid);
                    })
                    ->keys()
                    ->first();

                if (! $this->hasOrGroups()) {
                    return;
                }

                if (
                    $arguments['block'] !== 'filter_group' &&
                    $arguments['block'] !== 'and_group' &&
                    count($component->getLivewire()->{$tableFilters}['advanced_filter_builder']['or_group'][$parentUuid]['data']['and_group']) === 1
                ) {
                    $newUuid = (string) Str::random(3);

                    $newFilterGroup = 'advanced_filter_builder.or_group.' . $newUuid;

                    data_set($component->getLivewire()->{$tableFilters}, $newFilterGroup, [
                        'type' => 'filter_group',
                        'data' => [],
                    ]);
                }
            })
            ->livewireClickHandlerEnabled(false)
            ->button()
            ->size(ActionSize::Small);
    }

    public function getDeleteAction(): Action
    {
        return Action::make('delete')
            ->label(__('advanced-tables::filter-builder.form.remove_filter'))
            ->link()
            ->color('danger')
            ->action(function (array $arguments, Builder $component, $livewire): void {
                $this->removeFilter($arguments, $component, $livewire);

                if ($this->hasOrGroups()) {
                    return;
                }

                $tableFilters = $component->getLivewire()->getTable()->hasDeferredFilters()
                    ? 'tableDeferredFilters'
                    : 'tableFilters';

                if (! count($component->getLivewire()->{$tableFilters}['advanced_filter_builder']['or_group'])) {
                    $newUuid = (string) Str::random(3);

                    $newFilterGroup = 'advanced_filter_builder.or_group.' . $newUuid;

                    data_set($component->getLivewire()->{$tableFilters}, $newFilterGroup, [
                        'type' => 'filter_group',
                        'data' => [],
                    ]);
                }
            })
            ->size(ActionSize::Small);
    }

    public function getDeleteIconAction(): Action
    {
        return Action::make('deleteIcon')
            ->iconButton()
            ->icon('heroicon-s-trash')
            ->color('danger')
            ->action(function (array $arguments, Builder $component, $livewire): void {
                $this->removeFilter($arguments, $component, $livewire);
            })
            ->size(ActionSize::Small);
    }

    public function getExpandViewAction(): Action
    {
        return Action::make('expandView')
            ->label(__('advanced-tables::filter-builder.form.expand_view'))
            ->link()
            ->color('primary')
            ->fillForm(fn (Component $livewire) => $livewire->getTable()->hasDeferredFilters() ? $livewire->tableDeferredFilters : $livewire->tableFilters)
            ->form(fn (Component $livewire) => $livewire->getTable()->getFiltersForm())
            ->slideOver()
            ->modalHeading(__('filament-tables::table.filters.heading'))
            ->modalWidth('md')
            ->modalSubmitAction(false)
            ->extraModalFooterActions([
                Action::make('resetFilters')
                    ->label(__('filament-tables::table.filters.actions.reset.label'))
                    ->color('danger')
                    ->action('resetTableFiltersForm'),
            ])
            ->modalCancelActionLabel(__('filament::components/modal.actions.close.label'))
            ->size(ActionSize::Small);
    }

    public function defaultFilters(array | Closure $filters): static
    {
        $this->default(static function (AdvancedFilterBuilder $component) use ($filters): array {
            $filters = $component->evaluate($filters);

            if (! $filters) {
                return [[
                    'type' => 'filter_group',
                ]];
            }

            $defaultFilters = [];

            foreach ($filters as $index => $orGroup) {
                $defaultFilters[$index] = [
                    'type' => 'filter_group',
                ];

                foreach ($orGroup as $filter) {
                    $defaultFilters[$index]['data']['and_group'][] = [
                        'type' => $filter,
                        'data' => null,
                    ];
                }
            }

            return $defaultFilters;
        });

        return $this;
    }

    public function blockPickerMaxHeight(string | Closure | null $height): static
    {
        $this->blockPickerMaxHeight = $height;

        return $this;
    }

    public function blockPickerSearch(bool | Closure $condition): static
    {
        $this->blockPickerHasSearch = $condition;

        return $this;
    }

    public function orGroups(bool $condition): static
    {
        $this->hasOrGroups = $condition;

        return $this;
    }

    public function getBlockPickerMaxHeight(): ?string
    {
        return $this->evaluate($this->blockPickerMaxHeight);
    }

    public function blockPickerHasSearch(): bool
    {
        return $this->evaluate($this->blockPickerHasSearch);
    }

    public function hasWideFilterLayout(): bool
    {
        return $this->hasWideLayout($this->getLivewire());
    }

    public function hasOrGroups(): bool
    {
        return $this->hasOrGroups;
    }

    public function isModalLayout(): bool
    {
        return $this->getLivewire()->getTable()->getFiltersLayout() === FiltersLayout::Modal;
    }

    protected function removeFilter(array $arguments, Builder $component, $livewire): void
    {
        $uuid = $arguments['item'];

        $tableFilters = $component->getLivewire()->getTable()->hasDeferredFilters()
            ? 'tableDeferredFilters'
            : 'tableFilters';

        $parentUuid = collect($component->getLivewire()->{$tableFilters}['advanced_filter_builder']['or_group'])
            ->filter(function ($orGroup) use ($uuid) {
                return
                    array_key_exists('data', $orGroup) &&
                    array_key_exists('and_group', $orGroup['data']) &&
                    collect($orGroup['data']['and_group'])->has($uuid);
            })
            ->keys()
            ->first();

        $items = $component->getState();

        unset($items[$uuid]);

        $component->state($items);

        $livewire->resetActiveViewsIfRequired();

        invade($livewire)->handleTableFilterUpdates();

        if (! count($component->getLivewire()->{$tableFilters}['advanced_filter_builder']['or_group'][$parentUuid]['data']['and_group'])) {

            $tableFilters = $livewire->getTable()->hasDeferredFilters()
                ? 'tableDeferredFilters'
                : 'tableFilters';

            unset($livewire->{$tableFilters}['advanced_filter_builder']['or_group'][$parentUuid]);
        }
    }
}
