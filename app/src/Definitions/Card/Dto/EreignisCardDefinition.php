<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\EreignisPrerequisitesId;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Card\ValueObject\PhaseId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

final readonly class EreignisCardDefinition implements CardDefinition, CardWithYear, CardWithPhase, CardWithResourceChanges, CardWithModifiers
{
    /**
     * @param CardId $id
     * @param PileId $pileId
     * @param string $title
     * @param string $description
     * @param CategoryId $categoryId
     * @param PhaseId $phaseId
     * @param Year $year
     * @param ResourceChanges $resourceChanges
     * @param ModifierId[] $modifierIds
     * @param ModifierParameters $modifierParameters
     * @param EreignisPrerequisitesId[] $ereignisRequirementIds
     */
    public function __construct(
        protected CardId $id,
        protected PileId $pileId,
        protected string $title,
        protected string $description,
        protected CategoryId $categoryId,
        protected PhaseId $phaseId,
        protected Year $year,
        protected ResourceChanges $resourceChanges,
        protected array $modifierIds,
        protected ModifierParameters $modifierParameters,
        protected array $ereignisRequirementIds = [],
    ) {
    }

    public function getId(): CardId
    {
        return $this->id;
    }

    public function getPileId(): PileId
    {
        return $this->pileId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCategory(): CategoryId
    {
        return $this->categoryId;
    }

    public function getPhase(): PhaseId
    {
        return $this->phaseId;
    }

    public function getResourceChanges(): ResourceChanges
    {
        return $this->resourceChanges;
    }

    public function getYear(): Year
    {
        return $this->year;
    }

    public function getModifierIds(): array
    {
        return $this->modifierIds;
    }

    public function getModifierParameters(): ModifierParameters
    {
        return $this->modifierParameters;
    }
}
