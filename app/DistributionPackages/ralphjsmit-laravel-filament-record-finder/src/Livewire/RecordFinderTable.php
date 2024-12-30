<?php

namespace RalphJSmit\Filament\RecordFinder\Livewire;

use Closure;
use Filament\Actions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Livewire\Component;
use RalphJSmit\Filament\AutoTranslator\Contracts\HasTranslations;
use RalphJSmit\Filament\RecordFinder\Livewire\RecordFinderTable as Concerns;
use RalphJSmit\Filament\RecordFinder\Serialize;

// In order to add compatibility with the AutoTranslator plugin, this Livewire component will
// implement the `HasTranslations` interface. This allows us to forward any translation
// calls to the resource (if not standalone), so that the translations for that work.

if (! interface_exists('RalphJSmit\Filament\AutoTranslator\Contracts\HasTranslations')) {
    require __DIR__ . '/../AutoTranslator/Contracts/has_translations.php';
}

class RecordFinderTable extends Component implements \RalphJSmit\Filament\AutoTranslator\Contracts\HasTranslations, HasActions, HasForms, HasTable
{
    use Actions\Concerns\InteractsWithActions;
    use Concerns\HasAutoTranslatorSupport;
    use Forms\Concerns\InteractsWithForms;
    use Serialize\Concerns\HasUnserialization;
    use Tables\Concerns\InteractsWithTable;

    #[Locked]
    public string $tableQuerySource;

    #[Locked]
    public string $tableStandaloneSource;

    #[Locked]
    public string $tableColumnsSource;

    #[Locked]
    public string $tableGroupsSource;

    #[Locked]
    public string $tableDefaultGroupSource;

    #[Locked]
    public string $tableGroupingSettingsHiddenSource;

    #[Locked]
    public string $tableDeselectAllRecordsWhenFilteredSource;

    #[Locked]
    public string $tableFiltersSource;

    #[Locked]
    public string $tableHeaderActionsSource;

    #[Locked]
    public string $tableActionsSource;

    #[Locked]
    public string $tableBulkActionsSource;

    #[Locked]
    public string $tableEmptyStateActionsSource;

    #[Locked]
    public string $tablePaginationPageOptionsSource;

    #[Locked]
    public ?string $modifyTableCallbackSource;

    #[Locked]
    public string $modelLabel;

    #[Locked]
    public string $pluralModelLabel;

    #[Locked]
    public bool $isMultiple;

    public array $state;

    public function render(): View
    {
        return view('filament-record-finder::livewire.record-finder-table');
    }

    public function table(Tables\Table $table): Tables\Table
    {
        // In the case that a table should not inherit any properties from the model query resource (if found),
        // the table should be marked as standalone. Otherwise we will use the table configuration from the
        // underlying resource and override it with any of the custom provided table methods (if any).
        if (
            ! $this->getRecordFinderTableIsStandalone()
            && ($recordFinderTableQueryModelResource = $this->getRecordFinderTableQueryModelResource())
        ) {
            /** @var Tables\Table $table */
            $table = $recordFinderTableQueryModelResource::table($table)
                ->headerActions([])
                ->actions([])
                ->bulkActions([])
                ->emptyStateActions([]);
        }

        $table = $table->query($this->getRecordFinderTableQuery());

        if (filled($recordFinderTableColumns = $this->getRecordFinderTableColumns())) {
            $table = $table->columns($recordFinderTableColumns);
        }

        if (filled($recordFinderTableGroups = $this->getRecordFinderTableGroups())) {
            $table = $table->groups($recordFinderTableGroups);
        }

        if (filled($recordFinderTableDefaultGroup = $this->getRecordFinderTableDefaultGroup())) {
            $table = $table->defaultGroup($recordFinderTableDefaultGroup);
        }

        if (filled($recordFinderTableIsGroupingSettingsHidden = $this->getRecordFinderTableIsGroupingSettingsHidden())) {
            $table = $table->groupingSettingsHidden($recordFinderTableIsGroupingSettingsHidden);
        }

        if (filled($recordFinderTableShouldDeselectAllRecordsWhenFiltered = $this->getRecordFinderTableShouldDeselectAllRecordsWhenFiltered())) {
            $table = $table->deselectAllRecordsWhenFiltered($recordFinderTableShouldDeselectAllRecordsWhenFiltered);
        }

        if (filled($recordFinderTableFilters = $this->getRecordFinderTableFilters())) {
            $table = $table->filters($recordFinderTableFilters);
        }

        if (filled($recordFinderTableHeaderActions = $this->getRecordFinderTableHeaderActions())) {
            $table = $table->headerActions($recordFinderTableHeaderActions);
        }

        if (filled($recordFinderTableActions = $this->getRecordFinderTableActions())) {
            $table = $table->actions($recordFinderTableActions);
        }

        if (filled($recordFinderTableBulkActions = $this->getRecordFinderTableBulkActions())) {
            $table = $table->bulkActions($recordFinderTableBulkActions);
        }

        if (filled($recordFinderTableEmptyStateActions = $this->getRecordFinderTableEmptyStateActions())) {
            $table = $table->emptyStateActions($recordFinderTableEmptyStateActions);
        }

        if (filled($recordFinderTablePaginationPageOptions = $this->getRecordFinderTablePaginationPageOptions())) {
            $table = $table->paginationPageOptions($recordFinderTablePaginationPageOptions);
        }

        $table = $table
            ->selectable()
            ->description(
                $this->isMultiple
                    ? __('filament-record-finder::translations.livewire.record-finder-table.table.description.multiple', ['pluralModelLabel' => $this->pluralModelLabel])
                    : __('filament-record-finder::translations.livewire.record-finder-table.table.description.single', ['modelLabel' => $this->modelLabel])
            );

        if ($recordFinderModifyTableCallback = $this->getRecordFinderModifyTableCallback()) {
            $table = $table->evaluate(
                value: $recordFinderModifyTableCallback,
                namedInjections: [
                    'table' => $table,
                ],
                typedInjections: [
                    $table::class => $table,
                ]
            ) ?? $table;
        }

        return $table;
    }

    public function getRecordFinderTableQuery(): Builder
    {
        return $this->unserializeSource($this->tableQuerySource);
    }

    public function getRecordFinderTableIsStandalone(): bool
    {
        return $this->unserializeSource($this->tableStandaloneSource);
    }

    public function getRecordFinderTableColumns(): array
    {
        return $this->unserializeSource($this->tableColumnsSource);
    }

    public function getRecordFinderTableGroups(): array
    {
        return $this->unserializeSource($this->tableGroupsSource);
    }

    public function getRecordFinderTableDefaultGroup(): Tables\Grouping\Group | null | string
    {
        return $this->unserializeSource($this->tableDefaultGroupSource);
    }

    public function getRecordFinderTableShouldDeselectAllRecordsWhenFiltered(): bool
    {
        return $this->unserializeSource($this->tableDeselectAllRecordsWhenFilteredSource);
    }

    public function getRecordFinderTableIsGroupingSettingsHidden(): bool
    {
        return $this->unserializeSource($this->tableGroupingSettingsHiddenSource);
    }

    public function getRecordFinderTableFilters(): array
    {
        return $this->unserializeSource($this->tableFiltersSource);
    }

    public function getRecordFinderTableHeaderActions(): array
    {
        return $this->unserializeSource($this->tableHeaderActionsSource);
    }

    public function getRecordFinderTableActions(): array
    {
        return $this->unserializeSource($this->tableActionsSource);
    }

    public function getRecordFinderTableBulkActions(): array
    {
        return $this->unserializeSource($this->tableBulkActionsSource);
    }

    public function getRecordFinderTableEmptyStateActions(): array
    {
        return $this->unserializeSource($this->tableEmptyStateActionsSource);
    }

    public function getRecordFinderTablePaginationPageOptions(): ?array
    {
        return $this->unserializeSource($this->tablePaginationPageOptionsSource);
    }

    public function getRecordFinderModifyTableCallback(): ?Closure
    {
        return $this->modifyTableCallbackSource ? $this->unserializeSource($this->modifyTableCallbackSource) : null;
    }

    /**
     * @return class-string<Model>
     */
    protected function getRecordFinderTableQueryModel(): string
    {
        return $this->getRecordFinderTableQuery()->getModel()::class;
    }

    /**
     * @return class-string<resource>|null
     */
    protected function getRecordFinderTableQueryModelResource(): ?string
    {
        if (class_exists(\Filament\Facades\Filament::class) && ($panel = \Filament\Facades\Filament::getCurrentPanel())) {
            return $panel->getModelResource($this->getRecordFinderTableQueryModel());
        }

        return null;
    }
}
