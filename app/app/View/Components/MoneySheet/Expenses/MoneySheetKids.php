<?php

declare(strict_types=1);

namespace App\View\Components\MoneySheet\Expenses;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Illuminate\View\Component;
use Illuminate\View\View;

class MoneySheetKids extends Component
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
        return view('components.gameboard.moneySheet.expenses.money-sheet-kids', [
            'gehalt' => PlayerState::getGehaltForPlayer($this->gameStream, $this->playerId),
        ]);
    }

}
