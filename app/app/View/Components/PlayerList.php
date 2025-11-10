<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Livewire\Dto\PlayerListEmptySlotDto;
use App\Livewire\Dto\PlayerListPlayerDto;
use App\Livewire\Dto\Zeitsteine;
use App\Livewire\Dto\ZeitsteinWithColor;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
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

        return view('components.gameboard.playerList.player-list', [
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
                phaseDefinition: PlayerState::getCurrentLebenszielphaseDefinitionForPlayer($this->gameEvents, $playerId),
                lebenszielDefinition: PlayerState::getLebenszielDefinitionForPlayer($this->gameEvents, $playerId),
                sumOfAllAssets: PlayerState::getTotalValueOfAllAssetsForPlayer($this->gameEvents, $playerId),
                sumOfAllLoans: MoneySheetState::getTotalOpenRepaymentValueForAllLoans($this->gameEvents, $playerId),
                job: PlayerState::getJobForPlayer($this->gameEvents, $playerId),
                gehalt: PlayerState::getCurrentGehaltForPlayer($this->gameEvents, $playerId),
                guthaben: PlayerState::getGuthabenForPlayer($this->gameEvents, $playerId),
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
     * @return Zeitsteine
     */
    private function getZeitsteineForPlayer(PlayerId $playerId): Zeitsteine
    {
        $availableZeitsteine = PlayerState::getZeitsteineForPlayer($this->gameEvents, $playerId);
        $initialZeitsteine = KonjunkturphaseState::getInitialZeitsteineForCurrentKonjunkturphase($this->gameEvents);
        $playerName = PlayerState::getNameForPlayer($this->gameEvents, $playerId);

        $zeitsteine = [];
        for ($i = 0; $i < $initialZeitsteine; $i++) {
            $isAvailable = $i < $availableZeitsteine;
            $zeitstein = new ZeitsteinWithColor(
                drawEmpty: !$isAvailable,
                colorClass: PlayerState::getPlayerColorClass($this->gameEvents, $playerId),
            );
            $zeitsteine[] = $zeitstein;
        }

        return new Zeitsteine(
            ariaLabel: $playerName. ' hat noch ' . $availableZeitsteine . ' von ' . $initialZeitsteine . ' Zeitsteinen übrig.',
            mobileLabel: $availableZeitsteine . '/' . $initialZeitsteine,
            zeitsteine: $zeitsteine
        );
    }
}
