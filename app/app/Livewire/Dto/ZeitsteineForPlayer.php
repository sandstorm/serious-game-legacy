<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

use Domain\CoreGameLogic\PlayerId;

class ZeitsteineForPlayer
{
    public function __construct(
        public int $zeitsteine,
        public PlayerId $playerId,
    ) {}
}
