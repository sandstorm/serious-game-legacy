<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;

trait PlayerDetailsModalTrait
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
