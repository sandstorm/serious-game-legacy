<?php

declare(strict_types=1);

namespace App\View\Components\MoneySheet\Expenses;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
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
            'totalRepaymentValue' => new MoneyAmount(-1 * MoneySheetState::getTotalRepaymentValueForAllLoans($this->gameEvents, $this->playerId)->value),
            'sumOfRepaymentsPerRound' => new MoneyAmount(-1 * MoneySheetState::getAnnualExpensesForAllLoans($this->gameEvents, $this->playerId)->value),
        ]);
    }

}
