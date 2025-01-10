<?php

namespace RalphJSmit\Filament\RecordFinder\Forms\Components\RecordFinder;

use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

trait HasRecordState
{
    protected string|Closure|null $recordLabelAttribute = null;

    protected ?Closure $getRecordLabelFromRecordUsing = null;

    public function getRecordState(): null|Model|Collection
    {
        $state = $this->getState();

        if (blank($state)) {
            return null;
        }

        $optionModel = $this->getOptionModel();

        $record = $optionModel::find($state);

        if ($record instanceof Collection) {
            $record = $record->sortBy(fn (Model $record) => array_search($record->getKey(), $state));
        }

        return $record;
    }

    /**
     * @return class-string<Model>
     */
    public function getOptionModel(): string
    {
        $tableQuery = $this->getTableQuery();

        return $tableQuery->getModel()::class;
    }

    public function recordLabelAttribute(string|Closure|null $attribute): static
    {
        $this->recordLabelAttribute = $attribute;

        return $this;
    }

    public function getRecordLabelFromRecordUsing(?Closure $callback): static
    {
        $this->getRecordLabelFromRecordUsing = $callback;

        return $this;
    }

    public function getRecordLabelAttribute(Model $record): ?string
    {
        return $this->evaluate(
            $this->recordLabelAttribute,
            namedInjections: [
                'record' => $record,
                'state' => $record,
            ],
            typedInjections: [
                Model::class => $record,
                $record::class => $record,
            ]
        );
    }

    public function getRecordLabelFromRecord(Model $record): null|string|HtmlString
    {
        if ($this->recordLabelAttribute) {
            $recordLabelAttribute = $this->getRecordLabelAttribute($record);

            if ($recordLabelAttribute) {
                return $record->getAttribute($recordLabelAttribute);
            }
        }

        if ($this->getRecordLabelFromRecordUsing) {
            return $this->evaluate(
                $this->getRecordLabelFromRecordUsing,
                namedInjections: [
                    'record' => $record,
                    'state' => $record,
                ],
                typedInjections: [
                    Model::class => $record,
                    $record::class => $record,
                ]
            );
        }

        return null;
    }
}
