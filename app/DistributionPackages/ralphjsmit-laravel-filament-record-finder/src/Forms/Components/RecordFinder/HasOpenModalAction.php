<?php

namespace RalphJSmit\Filament\RecordFinder\Forms\Components\RecordFinder;

use Closure;
use Filament\Forms;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RalphJSmit\Filament\RecordFinder\Forms\Components\RecordFinder;
use RalphJSmit\Filament\RecordFinder\Forms\Components\RecordFinderReceiver;
use RalphJSmit\Filament\RecordFinder\Livewire\RecordFinderTable;
use RalphJSmit\Filament\RecordFinder\Serialize\Concerns\HasUnserialization;

trait HasOpenModalAction
{
    use HasUnserialization;

    protected ?Closure $modifyOpenModalActionUsing = null;

    protected string | Closure | null $openModalActionLabel = null;

    protected array | string | Closure | null $openModalActionColor = null;

    protected string | Closure | null $openModalActionIcon = 'heroicon-o-link';

    protected string | Htmlable | Closure | null $openModalActionModalHeading = null;

    protected string | Htmlable | Closure | null $openModalActionModalDescription = null;

    protected string | Closure | null $openModalActionModalSubmitActionLabel = null;

    protected bool | Closure $openModalActionIsModalSlideOver = false;

    protected MaxWidth | string | Closure | null $openModalActionModalWidth = null;

    protected string | Closure | null $recordFinderTableLivewireComponent = null;

    /**
     * @var array<string, mixed> | Closure
     */
    protected array | Closure $recordFinderTableLivewireComponentData = [];

    public function openModalAction(?Closure $callback): static
    {
        $this->modifyOpenModalActionUsing = $callback;

        return $this;
    }

    public function openModalActionLabel(null | string | Closure $label): static
    {
        $this->openModalActionLabel = $label;

        return $this;
    }

    public function openModalActionColor(array | string | Closure $label): static
    {
        $this->openModalActionColor = $label;

        return $this;
    }

    public function openModalActionIcon(string | Closure $label): static
    {
        $this->openModalActionIcon = $label;

        return $this;
    }

    public function modalHeading(string | Htmlable | Closure | null $heading): static
    {
        return $this->openModalActionModalHeading($heading);
    }

    public function openModalActionModalHeading(string | Htmlable | Closure | null $heading): static
    {
        $this->openModalActionModalHeading = $heading;

        return $this;
    }

    public function modalDescription(string | Htmlable | Closure | null $description): static
    {
        return $this->openModalActionModalDescription($description);
    }

    public function openModalActionModalDescription(string | Htmlable | Closure | null $description): static
    {
        $this->openModalActionModalDescription = $description;

        return $this;
    }

    public function modalSubmitActionLabel(null | string | Closure $label): static
    {
        return $this->openModalActionModalSubmitActionLabel($label);
    }

    public function openModalActionModalSubmitActionLabel(null | string | Closure $label): static
    {
        $this->openModalActionModalSubmitActionLabel = $label;

        return $this;
    }

    public function slideOver(bool | Closure $condition = true): static
    {
        return $this->openModalActionSlideOver($condition);
    }

    public function openModalActionSlideOver(bool | Closure $condition = true): static
    {
        $this->openModalActionIsModalSlideOver = $condition;

        return $this;
    }

    public function modalWidth(MaxWidth | string | Closure | null $width = null): static
    {
        return $this->openModalActionModalWidth($width);
    }

    public function openModalActionModalWidth(MaxWidth | string | Closure | null $width = null): static
    {
        $this->openModalActionModalWidth = $width;

        return $this;
    }

    public function recordFinderTableLivewireComponent(string | Closure | null $component): static
    {
        $this->recordFinderTableLivewireComponent = $component;

        return $this;
    }

    public function recordFinderTableLivewireComponentData(array | Closure $data): static
    {
        $this->recordFinderTableLivewireComponentData = $data;

        return $this;
    }

    public function getOpenModalActionColor(): array | string | null
    {
        return $this->evaluate($this->openModalActionColor);
    }

    public function getOpenModalActionIcon(): ?string
    {
        return $this->evaluate($this->openModalActionIcon);
    }

    public function getOpenModalActionLabel(): ?string
    {
        $modelLabel = $this->getModelLabel();
        $pluralModelLabel = $this->getPluralModelLabel();

        return $this->evaluate($this->openModalActionLabel, ['modelLabel' => $modelLabel, 'pluralModelLabel' => $pluralModelLabel])
            ?? trans_choice('filament-record-finder::translations.forms.components.record-finder.actions.open-modal-action.label', $this->isMultiple() ? 2 : 1, [
                'modelLabel' => $modelLabel,
                'pluralModelLabel' => $pluralModelLabel,
            ]);
    }

    public function getOpenModalActionModalHeading(): ?string
    {
        return $this->evaluate($this->openModalActionModalHeading);
    }

    public function getOpenModalActionModalDescription(): ?string
    {
        return $this->evaluate($this->openModalActionModalDescription);
    }

    public function getOpenModalActionModalSubmitActionLabel(): ?string
    {
        $modelLabel = $this->getModelLabel();
        $pluralModelLabel = $this->getPluralModelLabel();

        return $this->evaluate($this->openModalActionModalSubmitActionLabel, ['modelLabel' => $modelLabel, 'pluralModelLabel' => $pluralModelLabel])
            ?? trans_choice('filament-record-finder::translations.forms.components.record-finder.actions.open-modal-action.submit-action-label', $this->isMultiple() ? 2 : 1, [
                'modelLabel' => $modelLabel,
                'pluralModelLabel' => $pluralModelLabel,
            ]);
    }

    public function getOpenModalActionIsModalSlideOver(): bool
    {
        return (bool) $this->evaluate($this->openModalActionIsModalSlideOver);
    }

    public function getOpenModalActionModalWidth(): MaxWidth | string | null
    {
        return $this->evaluate($this->openModalActionModalWidth);
    }

    public function getOpenModalAction(): Forms\Components\Actions\Action
    {
        $action = Forms\Components\Actions\Action::make('openModal')
            ->label($this->getOpenModalActionLabel())
            ->color($this->getOpenModalActionColor())
            ->icon($this->getOpenModalActionIcon())
            ->modalHeading($this->getOpenModalActionModalHeading())
            ->modalDescription($this->getOpenModalActionModalDescription())
            ->modalSubmitActionLabel($this->getOpenModalActionModalSubmitActionLabel())
            ->slideOver($this->getOpenModalActionIsModalSlideOver())
            ->modalWidth($this->getOpenModalActionModalWidth())
            ->button()
            ->disabled($this->isDisabled());

        if ($this->modifyOpenModalActionUsing) {
            $action = $this->evaluate($this->modifyOpenModalActionUsing, [
                'action' => $action,
            ]) ?? $action;
        }

        return $action
            ->form(static function (Forms\Components\Actions\Action $action) {
                /** @var RecordFinder $component */
                $component = $action->getComponent();

                $recordFinderTableLivewireComponent = $component->getRecordFinderTableLivewireComponent() ?? RecordFinderTable::class;

                return [
                    Forms\Components\Hidden::make('record_finder_table_key')
                        ->default(Str::random(8))
                        ->dehydrated(false),
                    Forms\Components\Livewire::make($recordFinderTableLivewireComponent, [
                        'tableQuerySource' => $component->serializeOriginal($component->getTableQuery()),
                        'tableStandaloneSource' => $component->serializeOriginal($component->isTableStandalone()),
                        'tableColumnsSource' => $component->serializeOriginal($component->getTableColumns()),
                        'tableGroupsSource' => $component->serializeOriginal($component->getTableGroups()),
                        'tableDefaultGroupSource' => $component->serializeOriginal($component->getTableDefaultGroup()),
                        'tableDeselectAllRecordsWhenFilteredSource' => $component->serializeOriginal($component->shouldTableDeselectAllRecordsWhenFiltered()),
                        'tableGroupingSettingsHiddenSource' => $component->serializeOriginal($component->areTableGroupingSettingsHidden()),
                        'tableFiltersSource' => $component->serializeOriginal($component->getTableFilters()),
                        'tableHeaderActionsSource' => $component->serializeOriginal($component->getTableHeaderActions()),
                        'tableActionsSource' => $component->serializeOriginal($component->getTableActions()),
                        'tableBulkActionsSource' => $component->serializeOriginal($component->getTableBulkActions()),
                        'tableEmptyStateActionsSource' => $component->serializeOriginal($component->getTableEmptyStateActions()),
                        'tablePaginationPageOptionsSource' => $component->serializeOriginal($component->getTablePaginationPageOptions()),
                        'modifyTableCallbackSource' => $component->serializeOriginal($component->getModifyTableCallback()),
                        'modelLabel' => $component->getModelLabel(),
                        'pluralModelLabel' => $component->getPluralModelLabel(),
                        'isMultiple' => $component->isMultiple(),
                        'state' => Arr::wrap($component->getState() ?? []),
                        ...$component->getRecordFinderTableLivewireComponentData(),
                    ])
                        // Note: this method is only called once when the modal is opened. So having a `Str::random()` would be enough.
                        // However, there could be a situation that a Livewire request is made to the parent component using eg a
                        // `->extraModalFooterActions()` action. Therefore, we will cache the key in the `Hidden` component.
                        ->key(fn (Forms\Get $get) => $component->getKey() . '.actions.openModal.form.record_finder_table:' . $get('record_finder_table_key')),
                    RecordFinderReceiver::make('selected_records')
                        ->default(Arr::wrap($component->getState() ?? []))
                        ->hiddenLabel()
                        ->rules([
                            'array',
                            ...$component->isMultiple() ? [] : ['max:1'],
                        ])
                        ->required($component->isRequired()),
                ];
            })
            ->action(static function (array $data, self $component) {
                $selectedRecords = $data['selected_records'];

                if ($component->isMultiple()) {
                    $component->state($selectedRecords);
                } else {
                    $component->state($selectedRecords ? Arr::first($selectedRecords) : []);
                }

                $component->callAfterStateUpdated();
            });
    }

    public function getRecordFinderTableLivewireComponent(): ?string
    {
        return $this->evaluate($this->recordFinderTableLivewireComponent);
    }

    public function getRecordFinderTableLivewireComponentData(): array
    {
        return $this->evaluate($this->recordFinderTableLivewireComponentData);
    }
}
