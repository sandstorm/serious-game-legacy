<?php

declare(strict_types=1);

namespace App\View\Components\Konjunkturphase;

use App\Livewire\Dto\MoneySheet as MoneySheetDto;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Illuminate\View\Component;
use Illuminate\View\View;

class Summary extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        public GameEvents $gameEvents,
        public PlayerId $playerId,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.konjunkturphase.summary', [
            'moneySheet' => new MoneySheetDto(
                lebenshaltungskosten: MoneySheetState::getLastInputForLebenshaltungskosten($this->gameEvents, $this->playerId),
                doesLebenshaltungskostenRequirePlayerAction: MoneySheetState::doesLebenshaltungskostenRequirePlayerAction($this->gameEvents, $this->playerId),
                steuernUndAbgaben: MoneySheetState::getLastInputForSteuernUndAbgaben($this->gameEvents, $this->playerId),
                doesSteuernUndAbgabenRequirePlayerAction: MoneySheetState::doesSteuernUndAbgabenRequirePlayerAction($this->gameEvents, $this->playerId),
                gehalt: PlayerState::getCurrentGehaltForPlayer($this->gameEvents, $this->playerId),
                total: MoneySheetState::hasPlayerCompletedMoneysheet($this->gameEvents, $this->playerId) ? MoneySheetState::calculateTotalForPlayer($this->gameEvents, $this->playerId) : new MoneyAmount(0),
                totalInsuranceCost: MoneySheetState::getCostOfAllInsurances($this->gameEvents, $this->playerId),
                sumOfAllLoans: MoneySheetState::getSumOfAllLoansForPlayer($this->gameEvents, $this->playerId),
                sumOfAllAssets: PlayerState::getTotalValueOfAllAssetsForPlayer($this->gameEvents, $this->playerId),
                annualIncome: MoneySheetState::getAnnualIncomeForPlayer($this->gameEvents,  $this->playerId),
                annualExpenses: new MoneyAmount(-1 * MoneySheetState::getAnnualExpensesForPlayer($this->gameEvents, $this->playerId)->value),
            ),
        ]);
    }

}
