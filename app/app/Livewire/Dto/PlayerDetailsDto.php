<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\Definitions\Lebensziel\LebenszielDefinition;

class PlayerDetailsDto
{
    public function __construct(
        public string $name,
        public PlayerId $playerId,
        public LebenszielDefinition $lebensziel,
        public int $guthaben,
    ) {
    }

}
