<?php

namespace RalphJSmit\Filament\RecordFinder\Forms\Components\RecordFinder;

use Closure;
use Filament\Support\Services\RelationshipJoiner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Znck\Eloquent\Relations\BelongsToThrough;

/**
 * Trait is inspired by the default `Select` component from Filament, and
 * tweaked by removing all code unnecessary for the Record Finder (like
 * stuff relating to search and getting option labels). The below is
 * the resulting implementation after all the items were stripped.
 */
trait HasRelationship
{
    protected string | Closure | null $relationship = null;

    /**
     * @var array<string, mixed> | Closure
     */
    protected array | Closure $pivotData = [];

    public function relationship(string | Closure | null $name = null, ?Closure $modifyQueryUsing = null, bool $ignoreRecord = false): static
    {
        $this->relationship = $name ?? $this->getName();

        $this->tableQuery(static function (self $component) use ($modifyQueryUsing, $ignoreRecord): Builder {
            $relationship = Relation::noConstraints(fn () => $component->getRelationship());

            $relationshipQuery = app(RelationshipJoiner::class)->prepareQueryForNoConstraints($relationship);

            if ($ignoreRecord && ($record = $component->getRecord())) {
                $relationshipQuery->where($record->getQualifiedKeyName(), '!=', $record->getKey());
            }

            if ($modifyQueryUsing) {
                $relationshipQuery = $component->evaluate($modifyQueryUsing, [
                    'query' => $relationshipQuery,
                ]) ?? $relationshipQuery;
            }

            return $relationshipQuery;
        });

        $this->loadStateFromRelationshipsUsing(static function (self $component, $state) use ($modifyQueryUsing): void {
            if (filled($state)) {
                return;
            }

            $relationship = $component->getRelationship();
            $relationshipReorderColumn = $component->getRelationshipReorderColumn();

            if (
                ($relationship instanceof BelongsToMany) ||
                ($relationship instanceof HasManyThrough)
            ) {
                if ($modifyQueryUsing) {
                    $component->evaluate($modifyQueryUsing, [
                        'query' => $relationship->getQuery(),
                    ]);
                }

                if ($relationshipReorderColumn) {
                    $relationship instanceof BelongsToMany
                        ? $relationship->orderByPivot($relationshipReorderColumn)
                        : $relationship->orderBy($relationshipReorderColumn);
                }

                /** @var Collection $relatedRecords */
                $relatedRecords = $relationship->getResults();

                $component->state(
                    // Cast the related keys to a string, otherwise JavaScript does not
                    // know how to handle deselection.
                    //
                    // https://github.com/filamentphp/filament/issues/1111
                    $relatedRecords
                        ->pluck(($relationship instanceof BelongsToMany) ? $relationship->getRelatedKeyName() : $relationship->getRelated()->getKeyName())
                        ->map(static fn ($key): string => strval($key))
                        ->all(),
                );

                return;
            }

            if ($relationship instanceof BelongsToThrough) {
                $relatedModel = $relationship->getResults();

                $component->state(
                    $relatedModel->getAttribute(
                        $relationship->getRelated()->getKeyName(),
                    ),
                );

                return;
            }

            if ($relationshipReorderColumn) {
                $relationship->orderBy($relationshipReorderColumn);
            }

            /** @var BelongsTo $relationship */
            $relatedModel = $relationship->getResults();

            if (! $relatedModel) {
                return;
            }

            $component->state(
                $relatedModel->getAttribute(
                    $relationship->getOwnerKeyName(),
                ),
            );
        });

        $this->rule(
            static function (self $component): Exists {
                $relationship = $component->getRelationship();

                return Rule::exists(
                    $relationship->getModel()::class,
                    $component->getQualifiedRelatedKeyNameForRelationship($relationship),
                );
            },
            static function (self $component): bool {
                $relationship = $component->getRelationship();

                if (! (
                    $relationship instanceof BelongsTo ||
                    $relationship instanceof BelongsToThrough
                )) {
                    return false;
                }

                return ! $component->isMultiple();
            },
        );

        $this->saveRelationshipsUsing(static function (self $component, Model $record, $state) use ($modifyQueryUsing) {
            $relationship = $component->getRelationship();

            if (
                ($relationship instanceof HasOneOrMany) ||
                ($relationship instanceof HasManyThrough) ||
                ($relationship instanceof BelongsToThrough)
            ) {
                return;
            }

            if (! $relationship instanceof BelongsToMany) {
                // If the model is new and the foreign key is already filled, we don't need to fill it again.
                // This could be a security issue if the foreign key was mutated in some way before it
                // was saved, and we don't want to overwrite that value.
                if (
                    $record->wasRecentlyCreated &&
                    filled($record->getAttributeValue($relationship->getForeignKeyName()))
                ) {
                    return;
                }

                $relationship->associate($state);
                $record->wasRecentlyCreated && $record->save();

                return;
            }

            $relationshipReorderColumn = $component->getRelationshipReorderColumn();

            if ($modifyQueryUsing) {
                $component->evaluate($modifyQueryUsing, [
                    'query' => $relationship->getQuery(),
                ]);
            }

            /** @var Collection $relatedRecords */
            $relatedRecords = $relationship->getResults();

            $recordsToDetach = array_diff(
                $relatedRecords
                    ->pluck($relationship->getRelatedKeyName())
                    ->map(static fn ($key): string => strval($key))
                    ->all(),
                $state ?? [],
            );

            if (count($recordsToDetach) > 0) {
                $relationship->detach($recordsToDetach);
            }

            $pivotData = $component->getPivotData();

            if ($relationshipReorderColumn) {
                foreach ($state ?? [] as $stateKey) {
                    $order = ($order ?? 0) + 1;

                    $pivotAttributes = [
                        ...$pivotData,
                        $relationshipReorderColumn => $order,
                    ];

                    if ($relatedRecords->contains($stateKey)) {
                        $relationship->updateExistingPivot($stateKey, $pivotAttributes);
                    } else {
                        $relationship->attach($stateKey, $pivotAttributes);
                    }
                }
            } else {
                if ($pivotData === []) {
                    $relationship->sync($state ?? [], detaching: false);
                } else {
                    $relationship->syncWithPivotValues($state ?? [], $pivotData, detaching: false);
                }
            }
        });

        $this->dehydrated(fn (self $component): bool => ! $component->isMultiple());

        return $this;
    }

    /**
     * @param  array<string, mixed> | Closure  $data
     */
    public function pivotData(array | Closure $data): static
    {
        $this->pivotData = $data;

        return $this;
    }

    public function getRelationship(): BelongsTo | BelongsToMany | HasOneOrMany | HasManyThrough | BelongsToThrough | null
    {
        if (blank($this->getRelationshipName())) {
            return null;
        }

        $record = $this->getModelInstance();

        $relationship = null;

        foreach (explode('.', $this->getRelationshipName()) as $nestedRelationshipName) {
            if (! $record->isRelation($nestedRelationshipName)) {
                $relationship = null;

                break;
            }

            $relationship = $record->{$nestedRelationshipName}();
            $record = $relationship->getRelated();
        }

        return $relationship;
    }

    public function getRelationshipName(): ?string
    {
        return $this->evaluate($this->relationship);
    }

    /**
     * @return array<string, mixed>
     */
    public function getPivotData(): array
    {
        return $this->evaluate($this->pivotData) ?? [];
    }

    protected function getQualifiedRelatedKeyNameForRelationship(Relation $relationship): string
    {
        if ($relationship instanceof BelongsToMany) {
            return $relationship->getQualifiedRelatedKeyName();
        }

        if ($relationship instanceof HasManyThrough) {
            return $relationship->getQualifiedForeignKeyName();
        }

        if (
            ($relationship instanceof HasOneOrMany) ||
            ($relationship instanceof BelongsToThrough)
        ) {
            return $relationship->getRelated()->getQualifiedKeyName();
        }

        /** @var BelongsTo $relationship */

        return $relationship->getQualifiedOwnerKeyName();
    }
}
