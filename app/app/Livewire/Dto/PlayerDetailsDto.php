<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Lebensziel\Dto\LebenszielDefinition;

class PlayerDetailsDto
{
    public function __construct(
        public string $name,
        public PlayerId $playerId,
        public LebenszielDefinition $lebenszielDefinition,
        public float $guthaben,
        public int $zeitsteine,
        public int $kompetenzsteineBildung,
        public int $kompetenzsteineFreizeit,
        public int $currentLebenszielPhase,
    ) {
    }

}
