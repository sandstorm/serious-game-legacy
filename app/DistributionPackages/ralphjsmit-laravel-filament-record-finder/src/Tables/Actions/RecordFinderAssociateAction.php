<?php

namespace RalphJSmit\Filament\RecordFinder\Tables\Actions;

use Closure;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use RalphJSmit\Filament\RecordFinder\Forms\Components\RecordFinderReceiver;
use RalphJSmit\Filament\RecordFinder\Livewire\RecordFinderTable;
use RalphJSmit\Filament\RecordFinder\Serialize;
use RalphJSmit\Filament\RecordFinder\Tables\Actions\RecordFinderAssociateAction as Concerns;

class RecordFinderAssociateAction extends Action
{
    use CanCustomizeProcess;
    use Concerns\HasTable;
    use Concerns\IsMultiple;
    use Serialize\Concerns\HasSerialization;

    protected bool | Closure $canAssociateAnother = true;

    protected ?Closure $modifyRecordFinderTableQueryUsing = null;

    protected string | Closure | null $recordFinderTableLivewireComponent = null;

    /**
     * @var array<string, mixed> | Closure
     */
    protected array | Closure $recordFinderTableLivewireComponentData = [];

    public static function getDefaultName(): ?string
    {
        return 'associate';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('filament-record-finder::translations.tables.actions.associate.label'))
            ->modalHeading(fn (self $action) => __('filament-record-finder::translations.tables.actions.associate.modal_heading', ['modelLabel' => $action->getModelLabel()]))
            ->modalSubmitActionLabel(__('filament-record-finder::translations.tables.actions.associate.modal_submit_action_label'))
            ->modalWidth(MaxWidth::FiveExtraLarge)
            ->extraModalFooterActions(function (): array {
                return $this->canAssociateAnother()
                    ? [
                        $this
                            ->makeModalSubmitAction('associateAnother', arguments: ['another' => true])
                            ->label(__('filament-record-finder::translations.tables.actions.associate.extra_modal_footer_actions.associate_another.label')),
                    ]
                    : [];
            })
            ->successNotificationTitle(__('filament-record-finder::translations.tables.actions.associate.success_notification_title'))
            ->color('gray')
            ->tableQuery(function (self $action, Tables\Table $table) {
                $relationship = Relation::noConstraints(fn () => $table->getRelationship());

                $relationshipQuery = $relationship->getQuery();

                if ($this->modifyRecordFinderTableQueryUsing) {
                    $relationshipQuery = $this->evaluate($this->modifyRecordFinderTableQueryUsing, [
                        'query' => $relationshipQuery,
                    ]) ?? $relationshipQuery;
                }

                $relationCountHash = $relationship->getRelationCountHash(incrementJoinCount: false);

                if ($relationship instanceof MorphMany) {
                    $relationshipQuery->whereNotMorphedTo($table->getInverseRelationship(), $relationship->getParent());
                } else {
                    $relationshipQuery
                        ->whereDoesntHave($table->getInverseRelationship(), fn (Builder $query): Builder => $query->where(
                            // https://github.com/filamentphp/filament/issues/8067
                            $relationship->getParent()->getTable() === $relationship->getRelated()->getTable() ?
                                "{$relationCountHash}.{$relationship->getParent()->getKeyName()}" :
                                $relationship->getParent()->getQualifiedKeyName(),
                            $relationship->getParent()->getKey(),
                        ));
                }

                return $relationshipQuery;
            })
            ->form(static function (self $action) {
                $recordFinderTableLivewireComponent = $action->getRecordFinderTableLivewireComponent() ?? RecordFinderTable::class;

                return [
                    Forms\Components\Livewire::make($recordFinderTableLivewireComponent, [
                        'tableQuerySource' => $action->serializeOriginal($action->getTableQuery()),
                        'tableStandaloneSource' => $action->serializeOriginal($action->isTableStandalone()),
                        'tableColumnsSource' => $action->serializeOriginal($action->getTableColumns()),
                        'tableGroupsSource' => $action->serializeOriginal($action->getTableGroups()),
                        'tableDefaultGroupSource' => $action->serializeOriginal($action->getTableDefaultGroup()),
                        'tableDeselectAllRecordsWhenFilteredSource' => $action->serializeOriginal($action->shouldTableDeselectAllRecordsWhenFiltered()),
                        'tableGroupingSettingsHiddenSource' => $action->serializeOriginal($action->areTableGroupingSettingsHidden()),
                        'tableFiltersSource' => $action->serializeOriginal($action->getTableFilters()),
                        'tableHeaderActionsSource' => $action->serializeOriginal($action->getTableHeaderActions()),
                        'tableActionsSource' => $action->serializeOriginal($action->getTableActions()),
                        'tableBulkActionsSource' => $action->serializeOriginal($action->getTableBulkActions()),
                        'tableEmptyStateActionsSource' => $action->serializeOriginal($action->getTableEmptyStateActions()),
                        'tablePaginationPageOptionsSource' => $action->serializeOriginal($action->getTablePaginationPageOptions()),
                        'modifyTableCallbackSource' => $action->serializeOriginal($action->getModifyTableCallback()),
                        'modelLabel' => $action->getModelLabel(),
                        'pluralModelLabel' => $action->getPluralModelLabel(),
                        'isMultiple' => $action->isMultiple(),
                        'state' => [],
                        ...$action->getRecordFinderTableLivewireComponentData(),
                    ])
                        ->key($action->getName() . '.form.record_finder_table'),
                    RecordFinderReceiver::make('selected_records')
                        ->default([])
                        ->hiddenLabel()
                        ->rules([
                            'array',
                            ...$action->isMultiple() ? [] : ['max:1'],
                        ])
                        ->required(),
                ];
            })
            ->authorize(function (Tables\Contracts\HasTable $livewire, Tables\Table $table) {
                if ($livewire instanceof RelationManager) {
                    return ! $livewire->isReadOnly() && invade($livewire)->canAssociate();
                }

                return true;
            })
            ->action(function (array $arguments, array $data, Forms\Form $form, Tables\Table $table) {
                /** @var HasMany | MorphMany $relationship */
                $relationship = Relation::noConstraints(fn () => $table->getRelationship());

                $record = $relationship->getQuery()->whereKey($data['selected_records'])->get();

                foreach ($record as $record) {
                    if ($record instanceof Model) {
                        $this->record($record);
                    }

                    /** @var BelongsTo $inverseRelationship */
                    $inverseRelationship = $table->getInverseRelationshipFor($record);

                    $this->process(function () use ($inverseRelationship, $record, $relationship) {
                        $inverseRelationship->associate($relationship->getParent());
                        $record->save();
                    }, [
                        'inverseRelationship' => $inverseRelationship,
                        'relationship' => $relationship,
                    ]);
                }

                if ($arguments['another'] ?? false) {
                    $this->callAfter();
                    $this->sendSuccessNotification();

                    $this->record(null);

                    $form->fill();

                    $this->halt();

                    return;
                }

                $this->success();
            });
    }

    public function associateAnother(bool | Closure $condition = true): static
    {
        $this->canAssociateAnother = $condition;

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

    public function recordFinderTableQuery(?Closure $callback): static
    {
        $this->modifyRecordFinderTableQueryUsing = $callback;

        return $this;
    }

    public function canAssociateAnother(): bool
    {
        return (bool) $this->evaluate($this->canAssociateAnother);
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
