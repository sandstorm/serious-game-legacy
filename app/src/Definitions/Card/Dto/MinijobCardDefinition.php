<?php
declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

final readonly class MinijobCardDefinition implements CardDefinition, CardWithResourceChanges
{
    public function __construct(
        protected CardId $id,
        protected string $title,
        protected string $description,
        protected ResourceChanges $resourceChanges,
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
        return CategoryId::MINIJOBS;
    }

    public function getResourceChanges(): ResourceChanges
    {
        return $this->resourceChanges;
    }

    public function getPhase(): LebenszielPhaseId
    {
        return LebenszielPhaseId::ANY_PHASE;
    }
}
