<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

use App\Models\Game;

class Games
{
    /**
     * @param Game $game
     * @param array<string|null> $playerNames
     * @param bool $isInGamePhase
     */
    public function __construct(
        public Game  $game,
        public array $playerNames,
        public bool $isInGamePhase = false,
    )
    {
    }
}
