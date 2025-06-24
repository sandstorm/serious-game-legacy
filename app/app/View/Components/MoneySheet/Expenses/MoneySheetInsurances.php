<?php

declare(strict_types=1);

namespace App\View\Components\MoneySheet\Expenses;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Insurance\InsuranceFinder;
use Illuminate\View\Component;
use Illuminate\View\View;

class MoneySheetInsurances extends Component
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
        $insurances = InsuranceFinder::getAllInsurances();

        return view('components.gameboard.moneySheet.expenses.money-sheet-insurances', [
            'insurances' => $insurances,
        ]);
    }

}
