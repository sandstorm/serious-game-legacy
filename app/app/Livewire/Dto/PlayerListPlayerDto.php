<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

use Domain\CoreGameLogic\PlayerId;

class PlayerListPlayerDto
{
    /**
     * @param ZeitsteinWithColor[] $zeitsteine
     */
    public function __construct(
        public string   $name,
        public PlayerId $playerId,
        public string   $playerColorClass,
        public bool     $isPlayersTurn,
        public array    $zeitsteine,
        public int      $phase,
    )
    {
    }

}
