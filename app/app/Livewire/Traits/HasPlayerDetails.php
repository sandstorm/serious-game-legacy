<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use Domain\CoreGameLogic\PlayerId;

trait HasPlayerDetails
{
    public ?PlayerId $showLebenszielForPlayer = null;

    public function showPlayerLebensziel(string $playerId): void
    {
        $this->showLebenszielForPlayer = PlayerId::fromString($playerId);
    }

    public function closePlayerLebensziel(): void
    {
        $this->showLebenszielForPlayer = null;
    }
}
