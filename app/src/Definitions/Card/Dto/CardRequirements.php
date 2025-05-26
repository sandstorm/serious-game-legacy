<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

final readonly class CardRequirements
{
    public function __construct(
        public int $guthaben = 0,
        public int $zeitsteine = 0,
        public int $bildungKompetenzsteine = 0,
        public int $freizeitKompetenzsteine = 0,
    )
    {
    }
}
