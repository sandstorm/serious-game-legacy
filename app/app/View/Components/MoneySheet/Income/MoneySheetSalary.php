<?php

declare(strict_types=1);

namespace App\View\Components\MoneySheet\Income;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Illuminate\View\Component;
use Illuminate\View\View;

class MoneySheetSalary extends Component
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
        return view('components.gameboard.moneySheet.income.money-sheet-salary', [
            'jobDefinition' => PlayerState::getJobForPlayer($this->gameStream, $this->playerId),
            'gameStream' =>  $this->gameStream,
            'playerId' =>  $this->playerId,
        ]);
    }

}
