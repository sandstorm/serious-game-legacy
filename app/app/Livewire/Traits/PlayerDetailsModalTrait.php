<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;

trait PlayerDetailsModalTrait
{
    public ?PlayerId $currentPlayerId;

    public function showPlayerDetails(string $playerId): void
    {
        $this->currentPlayerId = PlayerId::fromString($playerId);
    }

    public function closePlayerDetails(): void
    {
        $this->currentPlayerId = null;
    }
}
