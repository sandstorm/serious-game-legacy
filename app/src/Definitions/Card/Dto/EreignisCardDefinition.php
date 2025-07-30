<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\EreignisPrerequisitesId;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

final readonly class EreignisCardDefinition implements CardDefinition, CardWithYear, CardWithResourceChanges, CardWithModifiers
{
    /**
     * @param CardId $id
     * @param string $title
     * @param string $description
     * @param CategoryId $categoryId
     * @param LebenszielPhaseId $phaseId
     * @param Year $year
     * @param ResourceChanges $resourceChanges
     * @param ModifierId[] $modifierIds
     * @param ModifierParameters $modifierParameters
     * @param EreignisPrerequisitesId[] $ereignisRequirementIds
     */
    public function __construct(
        protected CardId $id,
        protected CategoryId $categoryId,
        protected string $title,
        protected string $description,
        protected LebenszielPhaseId $phaseId = LebenszielPhaseId::PHASE_1,
        protected Year $year = new Year(1),
        protected ResourceChanges $resourceChanges = new ResourceChanges(),
        protected array $modifierIds = [],
        protected ModifierParameters $modifierParameters = new ModifierParameters(),
        protected array $ereignisRequirementIds = [],
    ) {
    }

    public function getId(): CardId
    {
        return $this->id;
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

    public function getPhase(): LebenszielPhaseId
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

    /**
     * @return EreignisPrerequisitesId[]
     */
    public function getEreignisRequirementIds(): array
    {
        return $this->ereignisRequirementIds;
    }
}
