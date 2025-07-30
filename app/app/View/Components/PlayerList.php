<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Livewire\Dto\PlayerListEmptySlotDto;
use App\Livewire\Dto\PlayerListPlayerDto;
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
        $players = $this->getPlayers();

        // if amount of players < 4, insert empty player slots
        $emptySlots = [];
        $amountOfPlayers = count($players);
        if ($amountOfPlayers < 4) {
            for ($i = 0; $i < 4 - $amountOfPlayers; $i++) {
                $emptySlots[] = new PlayerListEmptySlotDto(
                    playerColorClass: 'player-color-' . ($amountOfPlayers + $i + 1),
                );
            }
        }

        return view('components.gameboard.player-list', [
            'players' => $players,
            'emptySlots' => $emptySlots
        ]);
    }

    /**
     * @return PlayerListPlayerDto[]
     */
    private function getPlayers(): array
    {
        $orderedPlayers = GamePhaseState::getOrderedPlayers($this->gameEvents);
        $playerList = [];
        foreach ($orderedPlayers as $playerId) {
            $playerDto = new PlayerListPlayerDto(
                name: PlayerState::getNameForPlayer($this->gameEvents, $playerId),
                playerId: $playerId,
                playerColorClass: PlayerState::getPlayerColorClass($this->gameEvents, $playerId),
                isPlayersTurn: $playerId->equals($this->activePlayer),
                zeitsteine: $this->getZeitsteineForPlayer($playerId),
                phase: PlayerState::getCurrentLebenszielphaseIdForPlayer($this->gameEvents, $playerId)->value,
            );

            $playerList[] = $playerDto;
        }

        // sort myself to the end of the list
        usort($playerList, function (PlayerListPlayerDto $a, PlayerListPlayerDto $b) {
            if ($a->playerId->equals($this->myself)) {
                return 1; // move myself to the end
            }
            if ($b->playerId->equals($this->myself)) {
                return -1; // move myself to the end
            }
            return 0; // keep the order for other players
        });

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
