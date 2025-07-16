<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\EreignisPrerequisitesId;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Card\ValueObject\PileId;

final readonly class EreignisCardDefinition implements CardDefinition
{
    /**
     * @param CardId $id
     * @param PileId $pileId
     * @param string $title
     * @param string $description
     * @param ResourceChanges $resourceChanges
     * @param ModifierId[] $modifierIds
     * @param ModifierParameters $modifierParameters
     * @param EreignisPrerequisitesId[] $ereignisRequirementIds
     */
    public function __construct(
        public CardId $id,
        public PileId $pileId,
        public string $title,
        public string $description,
        public ResourceChanges $resourceChanges,
        public array $modifierIds,
        public ModifierParameters $modifierParameters,
        public array $ereignisRequirementIds = [],
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

    public function description(): string
    {
        return $this->description;
    }
}
