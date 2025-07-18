<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;

final readonly class JobCardDefinition implements CardDefinition
{
    public function __construct(
        public CardId           $id,
        public PileId           $pileId,
        public string           $title,
        public string           $description,
        public MoneyAmount      $gehalt,
        public JobRequirements  $requirements,
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
