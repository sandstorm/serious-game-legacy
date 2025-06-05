<?php

declare(strict_types=1);

namespace App\View\Components;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Illuminate\View\Component;
use Illuminate\View\View;
use App\Livewire\Dto\MoneySheet as MoneySheetDto;

class MoneySheet extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        public PlayerId $playerId,
        public GameEvents $gameStream,
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
            lebenskosten: MoneySheetState::lebenskostenForPlayer($this->gameStream, $playerId),
            gehalt: PlayerState::getJobForPlayer($this->gameStream, $playerId)?->gehalt->value ?? 0
        );
    }

}
