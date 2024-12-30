<?php

namespace Archilex\AdvancedTables\Filters\Concerns;

use Archilex\AdvancedTables\Filters\Operators\TextOperator;
use Closure;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Tables\Filters\Concerns\HasOptions;
use Filament\Tables\Filters\Concerns\HasPlaceholder;
use Filament\Tables\Filters\Concerns\HasRelationship;

trait HasSelect
{
    use HasOptions;
    use HasPlaceholder;
    use HasRelationship;

    protected string | Closure | null $attribute = null;

    protected bool | Closure $isMultiple = false;

    protected bool | Closure $isNative = true;

    /**
     * @var bool | array<string> | Closure
     */
    protected bool | array | Closure $searchable = false;

    protected int | Closure $optionsLimit = 50;

    protected ?Closure $getOptionLabelFromRecordUsing = null;

    protected bool $hasRelationship = false;

    public function relationship(string | Closure | null $name, string | Closure | null $titleAttribute, ?Closure $modifyQueryUsing = null): static
    {
        $this->hasRelationship = true;

        $this->relationship = $name;
        $this->relationshipTitleAttribute = $titleAttribute;
        $this->modifyRelationshipQueryUsing = $modifyQueryUsing;

        return $this;
    }

    public function attribute(string | Closure | null $name): static
    {
        $this->attribute = $name;

        return $this;
    }

    public function multiple(bool | Closure $condition = true): static
    {
        $this->isMultiple = $condition;

        return $this;
    }

    /**
     * @param  bool | array<string> | Closure  $condition
     */
    public function searchable(bool | array | Closure $condition = true): static
    {
        $this->searchable = $condition;

        return $this;
    }

    public function optionsLimit(int | Closure $limit): static
    {
        $this->optionsLimit = $limit;

        return $this;
    }

    public function native(bool | Closure $condition = true): static
    {
        $this->isNative = $condition;

        return $this;
    }

    public function getOptionLabelFromRecordUsing(?Closure $callback): static
    {
        $this->getOptionLabelFromRecordUsing = $callback;

        return $this;
    }

    public function isMultiple(): bool
    {
        return (bool) $this->evaluate($this->isMultiple);
    }

    /**
     * @return bool | array<string> | Closure
     */
    public function getSearchable(): bool | array | Closure
    {
        return $this->evaluate($this->searchable);
    }

    public function getAttribute(): string
    {
        return $this->evaluate($this->attribute) ?? $this->getName();
    }

    public function getOptionsLimit(): int
    {
        return $this->evaluate($this->optionsLimit);
    }

    public function isNative(): bool
    {
        return (bool) $this->evaluate($this->isNative);
    }

    protected function getSelectField(): Field
    {
        $field = Select::make($this->isMultiple() ? 'values' : 'value')
            ->hiddenLabel()
            ->multiple($this->isMultiple())
            ->placeholder($this->getPlaceholder())
            ->searchable($this->getSearchable())
            ->preload($this->isPreloaded())
            ->native($this->isNative())
            ->optionsLimit($this->getOptionsLimit())
            ->visible(fn (Get $get) => in_array($get('operator'), [TextOperator::IS, TextOperator::IS_NOT]))
            ->columnSpan([
                'sm' => 2,
            ]);

        if ($this->queriesRelationships() && blank($this->getOptions())) {
            $field
                ->relationship(
                    $this->getRelationshipName(),
                    $this->getRelationshipTitleAttribute(),
                    $this->modifyRelationshipQueryUsing,
                );
        } else {
            $field->options($this->getOptions());
        }

        if ($this->getOptionLabelUsing) {
            $field->getOptionLabelUsing($this->getOptionLabelUsing);
        }

        if ($this->getOptionLabelsUsing) {
            $field->getOptionLabelsUsing($this->getOptionLabelsUsing);
        }

        if ($this->getOptionLabelFromRecordUsing) {
            $field->getOptionLabelFromRecordUsing($this->getOptionLabelFromRecordUsing);
        }

        if ($this->getSearchResultsUsing) {
            $field->getSearchResultsUsing($this->getSearchResultsUsing);
        }

        return $field;
    }
}
