<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\ImmobilienType;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

final readonly class ImmobilienCardDefinition implements CardDefinition, CardWithResourceChanges
{
    public function __construct(
        protected CardId $id,
        protected string $title,
        protected string $description,
        protected LebenszielPhaseId $phaseId,
        protected ResourceChanges $resourceChanges,
        protected MoneyAmount $annualRent = new MoneyAmount(0),
        protected ImmobilienType $immobilienTyp = ImmobilienType::WOHNUNG,
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
        return CategoryId::INVESTITIONEN;
    }

    public function getPhase(): LebenszielPhaseId
    {
        return $this->phaseId;
    }

    public function getResourceChanges(): ResourceChanges
    {
        return $this->resourceChanges;
    }

    public function getAnnualRent(): MoneyAmount
    {
        return $this->annualRent;
    }

    public function getPurchasePrice(): MoneyAmount
    {
        return new MoneyAmount($this->getResourceChanges()->guthabenChange->value)->negate();
    }

    public function getImmobilienTyp(): ImmobilienType
    {
        return $this->immobilienTyp;
    }
}
