<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

use Domain\CoreGameLogic\PlayerId;

class PlayerListDto
{
    /**
     * @param PlayerZeitstein[] $zeitsteine
     */
    public function __construct(
        public string $name,
        public PlayerId $playerId,
        public string $playerColor,
        public bool $playersTurn = false,
        public array $zeitsteine = []
    ) {
    }

}
