<?php

declare(strict_types=1);

namespace App\View\Components\MoneySheet;

use App\Livewire\Dto\MoneySheet as MoneySheetDto;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Illuminate\View\Component;
use Illuminate\View\View;

class MoneySheet extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        public PlayerId $playerId,
        public GameEvents $gameEvents,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.gameboard.moneySheet.money-sheet', [
            'moneySheet' => $this->getMoneysheetForPlayerId($this->playerId),
        ]);
    }


    private function getMoneysheetForPlayerId(PlayerId $playerId): MoneySheetDto
    {
        return new MoneySheetDto(
            lebenshaltungskosten: MoneySheetState::getLastInputForLebenshaltungskosten($this->gameEvents, $playerId)->value,
            doesLebenshaltungskostenRequirePlayerAction: MoneySheetState::doesLebenshaltungskostenRequirePlayerAction($this->gameEvents, $playerId),
            steuernUndAbgaben: MoneySheetState::getLastInputForSteuernUndAbgaben($this->gameEvents, $playerId)->value,
            doesSteuernUndAbgabenRequirePlayerAction: MoneySheetState::doesSteuernUndAbgabenRequirePlayerAction($this->gameEvents, $playerId),
            gehalt: PlayerState::getGehaltForPlayer($this->gameEvents, $playerId)->value,
        );
    }

}
