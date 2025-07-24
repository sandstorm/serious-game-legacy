<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PhaseId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

final readonly class JobCardDefinition implements CardDefinition, CardWithPhase, CardWithYear
{
    public function __construct(
        protected CardId           $id,
        protected PileId           $pileId,
        protected string           $title,
        protected string           $description,
        protected PhaseId          $phaseId,
        protected Year             $year,
        protected MoneyAmount      $gehalt,
        protected JobRequirements  $requirements,
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
        return CategoryId::JOBS;
    }

    public function getPhase(): PhaseId
    {
        return $this->phaseId;
    }

    public function getYear(): Year
    {
        return $this->year;
    }
}
