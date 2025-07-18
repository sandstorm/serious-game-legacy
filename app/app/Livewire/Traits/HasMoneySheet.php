<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\Dto\MoneySheet as MoneySheetDto;
use App\Livewire\Forms\MoneySheetInsurancesForm;
use App\Livewire\Forms\MoneySheetLebenshaltungskostenForm;
use App\Livewire\Forms\MoneySheetSteuernUndAbgabenForm;
use App\Livewire\Forms\TakeOutALoanForm;
use App\Livewire\ValueObject\ExpensesTabEnum;
use App\Livewire\ValueObject\IncomeTabEnum;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Moneysheet\State\LoanCalculator;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanId;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CancelInsuranceForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ConcludeInsuranceForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterLebenshaltungskostenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\TakeOutALoanForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EnterSteuernUndAbgabenForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Insurance\InsuranceFinder;
use Domain\Definitions\Insurance\ValueObject\InsuranceId;
use Domain\Definitions\Konjunkturphase\ValueObject\AuswirkungScopeEnum;

trait HasMoneySheet
{
    // forms
    public MoneySheetLebenshaltungskostenForm $moneySheetLebenshaltungskostenForm;
    public MoneySheetSteuernUndAbgabenForm $moneySheetSteuernUndAbgabenForm;
    public MoneySheetInsurancesForm $moneySheetInsurancesForm;
    public TakeOutALoanForm $takeOutALoanForm;

    public bool $moneySheetIsVisible = false;
    public bool $editIncomeIsVisible = false;
    public bool $editExpensesIsVisible = false;
    public bool $takeOutALoanIsVisible = false;

    // set in the view money-sheet-income.blade.php
    public IncomeTabEnum $activeTabForIncome = IncomeTabEnum::INVESTMENTS;
    // set in the view money-sheet-expenses.blade.php
    public ExpensesTabEnum $activeTabForExpenses = ExpensesTabEnum::LOANS;

    public function mountHasMoneySheet(): void
    {
        // init insurances form
        $insurances = InsuranceFinder::getInstance()->getAllInsurances();
        $currentPlayerPhase = 1;
        foreach ($insurances as $insurance) {
            $isActive = MoneySheetState::doesPlayerHaveThisInsurance($this->gameEvents, $this->myself, $insurance->id);
            $this->moneySheetInsurancesForm->addInsurance(
                $currentPlayerPhase,
                $insurance,
                $isActive
            );
        }
    }

    /**
     * Update the form state on a rendering. Can happen for example when user changes their job.
     * Rerendering is triggered by Livewire when we use the broadcastNotify() method.
     *
     * @return void
     */
    public function renderingHasMoneySheet(): void
    {
        $latestInputForSteuernUndAbgaben = MoneySheetState::getLastInputForSteuernUndAbgaben($this->gameEvents, $this->myself);
        $calculatedSteuernUndAbgaben = MoneySheetState::calculateSteuernUndAbgabenForPlayer($this->gameEvents, $this->myself);
        $this->moneySheetSteuernUndAbgabenForm->steuernUndAbgaben = $latestInputForSteuernUndAbgaben->value;
        $this->moneySheetSteuernUndAbgabenForm->isSteuernUndAbgabenInputDisabled = $latestInputForSteuernUndAbgaben->equals($calculatedSteuernUndAbgaben);

        $latestInputForLebenshaltungskosten = MoneySheetState::getLastInputForLebenshaltungskosten($this->gameEvents, $this->myself);
        $calculatedLebenshaltungskosten = MoneySheetState::calculateLebenshaltungskostenForPlayer($this->gameEvents, $this->myself);
        $this->moneySheetLebenshaltungskostenForm->lebenshaltungskosten = $latestInputForLebenshaltungskosten->value;
        $this->moneySheetLebenshaltungskostenForm->isLebenshaltungskostenInputDisabled = $latestInputForLebenshaltungskosten->equals($calculatedLebenshaltungskosten);
    }

    public function showMoneySheet(): void
    {
        $this->moneySheetIsVisible = true;
    }

    public function closeMoneySheet(): void
    {
        $this->moneySheetIsVisible = false;
        $this->editIncomeIsVisible = false;
        $this->editExpensesIsVisible = false;
        $this->takeOutALoanIsVisible = false;
    }

    public function toggleEditIncome(): void
    {
        $this->editIncomeIsVisible = !$this->editIncomeIsVisible;
        if ($this->editIncomeIsVisible) {
            $this->showIncomeTab(IncomeTabEnum::INVESTMENTS->value);
        }
    }

    public function toggleEditExpenses(): void
    {
        $this->editExpensesIsVisible = !$this->editExpensesIsVisible;
        if ($this->editExpensesIsVisible) {
            $this->showExpensesTab(ExpensesTabEnum::LOANS->value);
        }
    }

    public function showIncomeTab(string $tab): void
    {
        $this->moneySheetIsVisible = true;
        $this->editIncomeIsVisible = true;
        $this->editExpensesIsVisible = false;
        $this->activeTabForIncome = IncomeTabEnum::from($tab);
        $this->takeOutALoanIsVisible = false;
    }

    public function showExpensesTab(string $tab): void
    {
        $this->moneySheetIsVisible = true;
        $this->editIncomeIsVisible = false;
        $this->editExpensesIsVisible = true;
        $this->activeTabForExpenses = ExpensesTabEnum::from($tab);
        $this->takeOutALoanIsVisible = false;
    }

    public function getMoneysheetForPlayerId(PlayerId $playerId): MoneySheetDto
    {
        return new MoneySheetDto(
            lebenshaltungskosten: MoneySheetState::getLastInputForLebenshaltungskosten($this->gameEvents, $playerId),
            doesLebenshaltungskostenRequirePlayerAction: MoneySheetState::doesLebenshaltungskostenRequirePlayerAction($this->gameEvents, $playerId),
            steuernUndAbgaben: MoneySheetState::getLastInputForSteuernUndAbgaben($this->gameEvents, $playerId),
            doesSteuernUndAbgabenRequirePlayerAction: MoneySheetState::doesSteuernUndAbgabenRequirePlayerAction($this->gameEvents, $playerId),
            gehalt: PlayerState::getBaseGehaltForPlayer($this->gameEvents, $playerId),
            total: MoneySheetState::calculateTotalForPlayer($this->gameEvents, $playerId),
            totalInsuranceCost: MoneySheetState::getCostOfAllInsurances($this->gameEvents, $playerId),
            sumOfAllLoans: MoneySheetState::getSumOfAllLoansForPlayer($this->gameEvents, $playerId),
            sumOfAllStocks: PlayerState::getTotalValueOfAllStocksForPlayer($this->gameEvents, $playerId),
        );
    }

    public function setLebenshaltungskosten(): void
    {
        $this->moneySheetLebenshaltungskostenForm->validate();
        $this->coreGameLogic->handle($this->gameId, EnterLebenshaltungskostenForPlayer::create(
            $this->myself,
            new MoneyAmount($this->moneySheetLebenshaltungskostenForm->lebenshaltungskosten)
        ));

        $updatedEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $resultOfLastInput = MoneySheetState::getResultOfLastLebenshaltungskostenInput($updatedEvents, $this->myself);

        if (!$resultOfLastInput->wasSuccessful && $resultOfLastInput->fine->value > 0) {
            $this->moneySheetLebenshaltungskostenForm->addError('lebenshaltungskosten',
                "Du hast einen falschen Wert für die Lebenshaltungskosten eingegeben. Dir wurden {$resultOfLastInput->fine->value} € abgezogen. Wir haben den Wert für dich korrigiert.");
            $this->moneySheetLebenshaltungskostenForm->isLebenshaltungskostenInputDisabled = true;
        } elseif (!$resultOfLastInput->wasSuccessful) {
            $this->moneySheetLebenshaltungskostenForm->addError('lebenshaltungskosten',
                "Du hast einen falschen Wert für die Lebenshaltungskosten eingegeben.");
        }

        $this->broadcastNotify();
    }

    public function setSteuernUndAbgaben(): void
    {
        $this->moneySheetSteuernUndAbgabenForm->validate();
        $this->coreGameLogic->handle($this->gameId, EnterSteuernUndAbgabenForPlayer::create(
            $this->myself,
            new MoneyAmount($this->moneySheetSteuernUndAbgabenForm->steuernUndAbgaben)
        ));

        $updatedEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $resultOfLastInput = MoneySheetState::getResultOfLastSteuernUndAbgabenInput($updatedEvents, $this->myself);

        if (!$resultOfLastInput->wasSuccessful && $resultOfLastInput->fine->value > 0) {
            $this->moneySheetSteuernUndAbgabenForm->addError('steuernUndAbgaben',
                "Du hast einen falschen Wert für die Steuern und Abgaben eingegeben. Dir wurden {$resultOfLastInput->fine->value} € abgezogen. Wir haben den Wert für dich korrigiert.");
            $this->moneySheetSteuernUndAbgabenForm->isSteuernUndAbgabenInputDisabled = true;
        } elseif (!$resultOfLastInput->wasSuccessful) {
            $this->moneySheetSteuernUndAbgabenForm->addError('steuernUndAbgaben',
                "Du hast einen falschen Wert für die Steuern und Abgaben eingegeben.");
        }

        $this->broadcastNotify();
    }

    public function setInsurances(): void
    {
        foreach($this->moneySheetInsurancesForm->insurances as $insuranceFromForm) {
            $insuranceId = InsuranceId::create($insuranceFromForm['id']);
            $currentlyConcluded = MoneySheetState::doesPlayerHaveThisInsurance($this->gameEvents, $this->myself, $insuranceId);
            $shouldBeConcluded = $insuranceFromForm['value'] === true;
            if ($currentlyConcluded === $shouldBeConcluded) {
                // nothing to do, insurance is already in the desired state
                continue;
            }
            // conclude or cancel insurance
            if ($shouldBeConcluded) {
                $this->coreGameLogic->handle($this->gameId, ConcludeInsuranceForPlayer::create($this->myself, $insuranceId));
            } else {
                $this->coreGameLogic->handle($this->gameId, CancelInsuranceForPlayer::create($this->myself, $insuranceId));
            }
        }

        $this->broadcastNotify();
    }

    public function showTakeOutALoan(): void
    {
        $this->showExpensesTab(ExpensesTabEnum::LOANS->value);
        $this->takeOutALoanIsVisible = true;
        $this->resetTakeOutALoanForm();
    }

    public function toggleTakeOutALoan(): void
    {
        $this->takeOutALoanIsVisible = !$this->takeOutALoanIsVisible;
        $this->resetTakeOutALoanForm();
    }

    private function resetTakeOutALoanForm(): void
    {
        $this->takeOutALoanForm->reset();
        $this->takeOutALoanForm->resetValidation();
        $this->takeOutALoanForm->loanId = LoanId::unique()->value;
        $this->takeOutALoanForm->guthaben = PlayerState::getGuthabenForPlayer($this->gameEvents, $this->myself)->value;
        $this->takeOutALoanForm->hasJob = PlayerState::getJobForPlayer($this->gameEvents, $this->myself) !== null;
        $this->takeOutALoanForm->zinssatz = KonjunkturphaseState::getCurrentKonjunkturphase($this->gameEvents)->getAuswirkungByScope(AuswirkungScopeEnum::LOANS_INTEREST_RATE)->modifier;
    }

    public function takeOutALoan(): void
    {
        $loanId = new LoanId($this->takeOutALoanForm->loanId);
        $this->coreGameLogic->handle($this->gameId, TakeOutALoanForPlayer::create(
            $this->myself,
            $this->takeOutALoanForm
        ));

        $updatedEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $resultOfLastInput = MoneySheetState::getResultOfLastLoanInput($updatedEvents, $this->myself, $loanId);

        if (!$resultOfLastInput->wasSuccessful && $resultOfLastInput->fine->value > 0) {
            $this->takeOutALoanForm->loanAmount = intval(min(
                $this->takeOutALoanForm->loanAmount,
                LoanCalculator::getMaxLoanAmount($this->takeOutALoanForm->guthaben, $this->takeOutALoanForm->hasJob)
            ));
            $this->takeOutALoanForm->totalRepayment = LoanCalculator::getCalculatedTotalRepayment($this->takeOutALoanForm->loanAmount, $this->takeOutALoanForm->zinssatz);
            $this->takeOutALoanForm->repaymentPerKonjunkturphase = $this->takeOutALoanForm->getCalculatedRepaymentPerKonjunkturphase();

            // reset old validation errors when correcting the input
            $this->takeOutALoanForm->resetValidation();
            $this->takeOutALoanForm->generalError = "Du hast falsche Werte für den Kredit eingegeben. Dir wurden {$resultOfLastInput->fine->value} € abgezogen. Wir haben die Werte für dich korrigiert.";
        } elseif (!$resultOfLastInput->wasSuccessful) {
            $this->takeOutALoanForm->generalError = "Du hast falsche Werte für den Kredit eingegeben.";
        } else {
            $this->takeOutALoanForm->resetValidation();
            $this->toggleTakeOutALoan();
        }

        $this->broadcastNotify();
    }
}
