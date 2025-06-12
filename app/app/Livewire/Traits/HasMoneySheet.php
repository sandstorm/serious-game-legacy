<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\Forms\MoneySheetLebenskostenForm;
use Domain\CoreGameLogic\Feature\Moneysheet\Command\EnterLebenshaltungskostenForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\LebenshaltungskostenForPlayerWereCorrected;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;

trait HasMoneySheet
{
    // forms
    public MoneySheetLebenskostenForm $moneySheetLebenskostenForm;

    public bool $moneySheetIsVisible = false;
    public bool $editIncomeIsVisible = false;
    public bool $editExpensesIsVisible = false;

    // set in the view money-sheet-income.blade.php
    public string $activeTabForIncome = 'investments'; // 'investments', 'salary'
    // set in the view money-sheet-expenses.blade.php
    public string $activeTabForExpenses = 'credits'; // 'credits', 'kids', 'insurances', 'taxes', 'livingCosts'

    /**
     * Prefixed with "mount" to avoid conflicts with Livewire's mount method.
     * Is automatically called by Livewire.
     * See https://livewire.laravel.com/docs/lifecycle-hooks#using-hooks-inside-a-trait
     *
     * @return void
     */
    public function mountHasMoneySheet(): void
    {
        $calculatedLebenskosten = MoneySheetState::calculateLebenshaltungskostenForPlayer($this->gameStream, $this->myself);
        $actualEnteredLebenskosten = MoneySheetState::getLastEnteredLebenshaltungskostenForPlayer($this->gameStream, $this->myself);

        if ($actualEnteredLebenskosten !== null && $calculatedLebenskosten !== $actualEnteredLebenskosten) {
            $this->moneySheetLebenskostenForm->addError('lebenskosten',
                'Deine Lebenshaltungskosten sind aktuell nicht korrekt eingegeben.');
        }

        $this->moneySheetLebenskostenForm->lebenskostenIsDisabled = $actualEnteredLebenskosten === $calculatedLebenskosten;
        $this->moneySheetLebenskostenForm->lebenskosten = $actualEnteredLebenskosten !== null ? $actualEnteredLebenskosten : 0;
    }

    /**
     * Update the form state on a rendering. Can happen for example when user changes their job.
     * Rerendering is triggered by the Livewire when we use the broadcastNotify() method.
     *
     * @return void
     */
    public function renderingHasMoneySheet(): void
    {
        $calculatedLebenskosten = MoneySheetState::calculateLebenshaltungskostenForPlayer($this->gameStream, $this->myself);
        $actualEnteredLebenskosten = MoneySheetState::getLastEnteredLebenshaltungskostenForPlayer($this->gameStream, $this->myself);

        // show error message when user made a mistake in the input
        // is done in the rerendering phase, because the event fired on the input change handles the 250 EUR deduction
        if ($calculatedLebenskosten !== $this->moneySheetLebenskostenForm->lebenskosten) {
            $fine = LebenshaltungskostenForPlayerWereCorrected::getFineForPlayer();
            $this->moneySheetLebenskostenForm->addError('lebenskosten',
                "Du hast einen falschen Wert für die Lebenshaltungskosten eingegeben. Es wurden dir $fine € abgezogen.");
        }

        $this->moneySheetLebenskostenForm->lebenskostenIsDisabled = $actualEnteredLebenskosten === $calculatedLebenskosten;
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
        }
    }

    public function toggleEditExpenses(): void
    {
        $this->editExpensesIsVisible = !$this->editExpensesIsVisible;
        if ($this->editExpensesIsVisible) {
            $this->editIncomeIsVisible = false;
        }
    }

    public function showSalaryTab(): void
    {
        $this->moneySheetIsVisible = true;
        $this->editIncomeIsVisible = true;
        $this->editExpensesIsVisible = false;
        $this->activeTabForIncome = 'salary';
    }

    public function setLebenskosten(): void
    {
        $actualEnteredLebenskosten = MoneySheetState::getLastEnteredLebenshaltungskostenForPlayer($this->gameStream, $this->myself);
        if ($actualEnteredLebenskosten !== null && $this->moneySheetLebenskostenForm->lebenskosten === $actualEnteredLebenskosten) {
            return; // no change, nothing to do
        }

        $this->moneySheetLebenskostenForm->validate();
        $this->coreGameLogic->handle($this->gameId, EnterLebenshaltungskostenForPlayer::create($this->myself, $this->moneySheetLebenskostenForm->lebenskosten));
        $this->broadcastNotify();
    }

}
