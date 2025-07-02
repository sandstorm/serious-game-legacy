<?php

declare(strict_types=1);

namespace App\View\Components\MoneySheet\Expenses;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Configuration\Configuration;
use Illuminate\View\Component;
use Illuminate\View\View;

class MoneySheetLoans extends Component
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
        return view('components.gameboard.moneySheet.expenses.money-sheet-loans', [
            'loans' => MoneySheetState::getLoansForPlayer($this->gameEvents, $this->playerId),
            'sumOfLoans' => MoneySheetState::getSumOfAllLoansForPlayer($this->gameEvents, $this->playerId),
            'repaymentPeriod' => Configuration::REPAYMENT_PERIOD
        ]);
    }

}
