<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use Domain\CoreGameLogic\PlayerId;

trait HasPlayerDetails
{
    public ?PlayerId $showDetailsForPlayer;

    public function showPlayerDetails(string $playerId): void
    {
        $this->showDetailsForPlayer = PlayerId::fromString($playerId);
    }

    public function closePlayerDetails(): void
    {
        $this->showDetailsForPlayer = null;
    }
}
