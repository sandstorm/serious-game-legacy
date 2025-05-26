<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\Definitions\Lebensziel\Dto\LebenszielDefinition;

class PlayerDetailsDto
{
    public function __construct(
        public string $name,
        public PlayerId $playerId,
        public LebenszielDefinition $lebensziel,
        public int $guthaben,
        public int $zeitsteine,
        public int $kompetenzsteineBildung,
        public int $kompetenzsteineFreizeit,
    ) {
    }

}
