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
     * @param bool $isPlayable false, if the game was created on a different version of the game
     */
    public function __construct(
        public Game  $game,
        public array $playerNames,
        public bool $isInGamePhase = false,
        public bool $isPlayable = true,
    ) {
    }
}
