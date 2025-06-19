<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Livewire\Dto\PlayerDetailsDto;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Illuminate\View\Component;
use Illuminate\View\View;

class PlayerDetails extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        public ?string $playerId,
        public GameEvents $gameEvents,
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

    private function getPlayerDetailsForPlayerId(?string $playerId): ?PlayerDetailsDto
    {
        if ($playerId === null) {
            return null;
        }

        $playerId = PlayerId::fromString($playerId);
        $lebensziel = PreGameState::lebenszielForPlayer($this->gameEvents, $playerId);

        return new PlayerDetailsDto(
            name: PreGameState::nameForPlayer($this->gameEvents, $playerId),
            playerId: $playerId,
            lebensziel: $lebensziel->definition,
            guthaben: PlayerState::getGuthabenForPlayer($this->gameEvents, $playerId),
            zeitsteine: PlayerState::getZeitsteineForPlayer($this->gameEvents, $playerId),
            kompetenzsteineBildung: PlayerState::getBildungsKompetenzsteine($this->gameEvents, $playerId),
            kompetenzsteineFreizeit: PlayerState::getFreizeitKompetenzsteine($this->gameEvents, $playerId),
        );
    }

}
