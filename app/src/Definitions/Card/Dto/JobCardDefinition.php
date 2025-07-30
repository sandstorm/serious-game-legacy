<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

final readonly class JobCardDefinition implements CardDefinition, CardWithYear
{
    public function __construct(
        protected CardId           $id,
        protected string           $title,
        protected string           $description,
        protected LebenszielPhaseId          $phaseId = LebenszielPhaseId::PHASE_1,
        protected Year             $year = new Year(1),
        protected MoneyAmount      $gehalt = new MoneyAmount(20000),
        protected JobRequirements  $requirements = new JobRequirements(),
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
        return CategoryId::JOBS;
    }

    public function getPhase(): LebenszielPhaseId
    {
        return $this->phaseId;
    }

    public function getYear(): Year
    {
        return $this->year;
    }

    public function getGehalt(): MoneyAmount
    {
        return $this->gehalt;
    }

    public function getRequirements(): JobRequirements
    {
        return $this->requirements;
    }
}
