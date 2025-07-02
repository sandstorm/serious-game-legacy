<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\Forms\MoneySheetInsurancesForm;
use App\Livewire\Forms\MoneySheetLebenshaltungskostenForm;
use App\Livewire\Forms\MoneySheetSteuernUndAbgabenForm;
use App\Livewire\Forms\TakeOutALoanForm;
use App\Livewire\ValueObject\ExpensesTabEnum;
use App\Livewire\ValueObject\IncomeTabEnum;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Moneysheet\Command\CancelInsuranceForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\Command\ConcludeInsuranceForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\Command\EnterLebenshaltungskostenForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\Command\EnterSteuernUndAbgabenForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\Command\TakeOutALoanForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Insurance\InsuranceFinder;
use Domain\Definitions\Insurance\ValueObject\InsuranceId;

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
    }

    public function toggleEditIncome(): void
    {
        $this->editIncomeIsVisible = !$this->editIncomeIsVisible;
        if ($this->editIncomeIsVisible) {
            $this->editExpensesIsVisible = false;
            $this->activeTabForIncome = IncomeTabEnum::INVESTMENTS; // reset to default tab when switching to income
        }
    }

    public function toggleEditExpenses(): void
    {
        $this->editExpensesIsVisible = !$this->editExpensesIsVisible;
        if ($this->editExpensesIsVisible) {
            $this->editIncomeIsVisible = false;
            $this->activeTabForExpenses = ExpensesTabEnum::LOANS; // reset to default tab when switching to expenses
        }
    }

    public function showIncomeTab(string $tab): void
    {
        $this->moneySheetIsVisible = true;
        $this->editIncomeIsVisible = true;
        $this->editExpensesIsVisible = false;
        $this->activeTabForIncome = IncomeTabEnum::from($tab);
    }

    public function showExpensesTab(string $tab): void
    {
        $this->moneySheetIsVisible = true;
        $this->editIncomeIsVisible = false;
        $this->editExpensesIsVisible = true;
        $this->activeTabForExpenses = ExpensesTabEnum::from($tab);
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

    public function toggleTakeOutALoan(): void
    {
        $this->takeOutALoanIsVisible = !$this->takeOutALoanIsVisible;
        $this->takeOutALoanForm->reset();
        $this->takeOutALoanForm->guthaben = PlayerState::getGuthabenForPlayer($this->gameEvents, $this->myself)->value;
        $this->takeOutALoanForm->zinssatz = KonjunkturphaseState::getCurrentKonjunkturphase($this->gameEvents)->zinssatz;
        $this->takeOutALoanForm->hasJob = PlayerState::getJobForPlayer($this->gameEvents, $this->myself) !== null;
    }

    public function takeOutALoan(): void
    {
        $this->takeOutALoanForm->validate();

        // TODO what happens if the player makes mistakes?
        $this->coreGameLogic->handle($this->gameId, TakeOutALoanForPlayer::create(
            $this->myself,
            $this->takeOutALoanForm->intendedUse,
            new MoneyAmount($this->takeOutALoanForm->loanAmount),
            new MoneyAmount($this->takeOutALoanForm->totalRepayment),
            new MoneyAmount($this->takeOutALoanForm->repaymentPerKonjunkturphase)
        ));

        $this->toggleTakeOutALoan();
        $this->broadcastNotify();
    }
}
