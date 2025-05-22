<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Livewire\Dto\PlayerDetailsDto;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\State\GuthabenState;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Illuminate\View\Component;
use Illuminate\View\View;

class PlayerDetails extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        public ?string $playerId,
        public GameEvents $gameStream,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.gameboard.player-details', [
            'id' => $this->playerId,
            'playerDetails' => $this->getPlayerDetailsForPlayerId($this->playerId),
        ]);
    }

    public function getIsVisible(): bool
    {
        return $this->playerId !== null;
    }

    private function getPlayerDetailsForPlayerId(?string $playerId): ?PlayerDetailsDto
    {
        if ($playerId === null) {
            return null;
        }

        $playerId = PlayerId::fromString($playerId);

        return new PlayerDetailsDto(
            name: PreGameState::nameForPlayer($this->gameStream, $playerId),
            playerId: $playerId,
            lebensziel: PreGameState::lebenszielForPlayer($this->gameStream, $playerId),
            guthaben: GuthabenState::forPlayer($this->gameStream, $playerId)->value,
        );
    }

}
