<?php

declare(strict_types=1);

namespace App\View\Components\MoneySheet\Expenses;

use App\Livewire\Dto\MoneySheet as MoneySheetDto;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\PlayerId;
use Illuminate\View\Component;
use Illuminate\View\View;

class MoneySheetExpenses extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        public MoneySheetDto $moneySheet,
        public PlayerId $playerId,
        public GameEvents $gameEvents,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.gameboard.moneySheet.expenses.money-sheet-expenses', [
            'moneySheet' => $this->moneySheet,
        ]);
    }

}
