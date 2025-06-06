<?php

declare(strict_types=1);

namespace App\View\Components;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\State\MoneySheetState;
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
        public string $playerId,
        public GameEvents $gameStream,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.gameboard.moneySheet.money-sheet', [
            'lebenskosten' => $this->getMoneysheetForPlayerId($this->playerId)?->lebenskosten,
        ]);
    }

    private function getMoneysheetForPlayerId(?string $playerId): ?MoneySheetDto
    {
        if ($playerId === null) {
            return null;
        }

        $playerId = PlayerId::fromString($playerId);
        $lebenskosten = MoneySheetState::lebenskostenForPlayer($this->gameStream, $playerId);

        return new MoneySheetDto(
            lebenskosten: $lebenskosten,
        );
    }

}
