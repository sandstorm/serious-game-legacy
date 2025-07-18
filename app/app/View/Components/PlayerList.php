<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Livewire\Dto\PlayerListDto;
use App\Livewire\Dto\ZeitsteinWithColor;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Illuminate\View\Component;
use Illuminate\View\View;

class PlayerList extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        public GameEvents $gameEvents,
        public PlayerId $myself,
        public PlayerId $activePlayer
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.gameboard.player-list', [
            'players' => $this->getPlayers(),
        ]);
    }

    /**
     * @return PlayerListDto[]
     */
    private function getPlayers(): array
    {
        $orderedPlayers = GamePhaseState::getOrderedPlayers($this->gameEvents);
        $playerList = [];
        foreach ($orderedPlayers as $playerId) {
            $playerList[] = new PlayerListDto(
                name: PlayerState::nameForPlayer($this->gameEvents, $playerId),
                playerId: $playerId,
                isPlayersTurn: $playerId->equals($this->activePlayer),
                zeitsteine: $this->getZeitsteineForPlayer($playerId),
            );
        }
        return $playerList;
    }

    /**
     * @param PlayerId $playerId
     * @return ZeitsteinWithColor[]
     */
    private function getZeitsteineForPlayer(PlayerId $playerId): array
    {
        $availableZeitsteine = PlayerState::getZeitsteineForPlayer($this->gameEvents, $playerId);
        $initialZeitsteine = KonjunkturphaseState::getInitialZeitsteineForCurrentKonjunkturphase($this->gameEvents);

        $zeitsteine = [];
        for ($i = 0; $i < $initialZeitsteine; $i++) {
            $isAvailable = $i < $availableZeitsteine;
            $zeitstein = new ZeitsteinWithColor(
                drawEmpty: !$isAvailable,
                colorClass: PlayerState::getPlayerColorClass($this->gameEvents, $playerId),
            );
            $zeitsteine[] = $zeitstein;
        }

        return $zeitsteine;
    }
}
