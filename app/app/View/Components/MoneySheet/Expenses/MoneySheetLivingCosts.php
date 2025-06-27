<?php

declare(strict_types=1);

namespace App\View\Components\MoneySheet\Expenses;

use App\Livewire\Dto\MoneySheet as MoneySheetDto;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Illuminate\View\Component;
use Illuminate\View\View;

class MoneySheetLivingCosts extends Component
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
        return view('components.gameboard.moneySheet.expenses.money-sheet-living-costs', [
            'moneySheet' => new MoneySheetDto(
                lebenshaltungskosten: MoneySheetState::calculateLebenshaltungskostenForPlayer($this->gameEvents, $this->playerId),
                doesLebenshaltungskostenRequirePlayerAction: MoneySheetState::doesLebenshaltungskostenRequirePlayerAction($this->gameEvents, $this->playerId),
                steuernUndAbgaben: MoneySheetState::calculateSteuernUndAbgabenForPlayer($this->gameEvents, $this->playerId),
                doesSteuernUndAbgabenRequirePlayerAction: MoneySheetState::doesSteuernUndAbgabenRequirePlayerAction($this->gameEvents, $this->playerId),
                gehalt: PlayerState::getGehaltForPlayer($this->gameEvents, $this->playerId)->value,
                total: MoneySheetState::calculateTotalForPlayer($this->gameEvents, $this->playerId)->value,
                totalInsuranceCost: MoneySheetState::getCostOfAllInsurances($this->gameEvents, $this->playerId),
                sumOfAllLoans: MoneySheetState::getSumOfAllLoansForPlayer($this->gameEvents, $this->playerId),
            ),
        ]);
    }

}
