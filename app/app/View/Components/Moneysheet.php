<?php

declare(strict_types=1);

namespace App\View\Components;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\State\MoneysheetState;
use Domain\CoreGameLogic\PlayerId;
use Illuminate\View\Component;
use Illuminate\View\View;

class Moneysheet extends Component
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
        return view('components.gameboard.moneysheet.moneysheet', [
            'lebenskosten' => $this->getMoneysheetForPlayerId($this->playerId)?->lebenskosten,
        ]);
    }

    private function getMoneysheetForPlayerId(?string $playerId): ?\App\Livewire\Dto\MoneySheet
    {
        if ($playerId === null) {
            return null;
        }

        $playerId = PlayerId::fromString($playerId);
        $lebenskosten = MoneysheetState::lebenskostenForPlayer($this->gameStream, $playerId);

        return new \App\Livewire\Dto\MoneySheet(
            lebenskosten: $lebenskosten,
        );
    }

}
