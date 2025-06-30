<?php

declare(strict_types=1);

namespace App\View\Components\MoneySheet\Expenses;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Insurance\InsuranceFinder;
use Domain\Definitions\Insurance\ValueObject\InsuranceTypeEnum;
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
            'playerCanTakeOutALoan' => $this->getPlayerCanTakeOutALoan(),
        ]);
    }

    /**
     * @return bool
     */
    public function getPlayerCanTakeOutALoan(): bool
    {
        // TODO maybe move this code in to some kind of "Aktion" to avoid having duplicate code in the command handler and here
        // player needs a job to take out a loan
        $playerHasJob = PlayerState::getJobForPlayer($this->gameEvents, $this->playerId);
        if ($playerHasJob === null) {
            return false;
        }

        // player needs Arbeitslosenversicherung
        $insurance = InsuranceFinder::getInstance()->findInsuranceByType(InsuranceTypeEnum::BERUFSUNFAEHIGKEITSVERSICHERUNG);
        $hasInsurance = MoneySheetState::doesPlayerHaveThisInsurance(
            $this->gameEvents,
            $this->playerId,
            $insurance->id
        );

        if (!$hasInsurance) {
            return false;
        }

        return true;
    }

}
