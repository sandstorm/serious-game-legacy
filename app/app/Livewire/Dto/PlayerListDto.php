<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

use Domain\CoreGameLogic\PlayerId;

class PlayerListDto
{
    /**
     * @param ZeitsteinWithColor[] $zeitsteine
     */
    public function __construct(
        public string   $name,
        public PlayerId $playerId,
        public bool     $isPlayersTurn = false,
        public array    $zeitsteine = []
    ) {
    }

}
