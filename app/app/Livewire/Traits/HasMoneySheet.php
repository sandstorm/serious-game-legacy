<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\Forms\MoneySheetLebenshaltungskostenForm;
use App\Livewire\Forms\MoneySheetSteuernUndAbgabenForm;
use Domain\CoreGameLogic\Feature\Moneysheet\Command\EnterLebenshaltungskostenForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\Command\EnterSteuernUndAbgabenForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;

trait HasMoneySheet
{
    // forms
    public MoneySheetLebenshaltungskostenForm $moneySheetLebenshaltungskostenForm;
    public MoneySheetSteuernUndAbgabenForm $moneySheetSteuernUndAbgabenForm;

    public bool $moneySheetIsVisible = false;
    public bool $editIncomeIsVisible = false;
    public bool $editExpensesIsVisible = false;

    // set in the view money-sheet-income.blade.php
    public string $activeTabForIncome = 'investments'; // 'investments', 'salary'
    // set in the view money-sheet-expenses.blade.php
    public string $activeTabForExpenses = 'credits'; // 'credits', 'kids', 'insurances', 'taxes', 'livingCosts'


    /**
     * Update the form state on a rendering. Can happen for example when user changes their job.
     * Rerendering is triggered by Livewire when we use the broadcastNotify() method.
     *
     * @return void
     */
    public function renderingHasMoneySheet(): void
    {
        $latestInputForSteuernUndAbgaben = MoneySheetState::getLastInputForSteuernUndAbgaben($this->gameStream, $this->myself);
        $calculatedSteuernUndAbgaben = MoneySheetState::calculateSteuernUndAbgabenForPlayer($this->gameStream,
            $this->myself);
        $this->moneySheetSteuernUndAbgabenForm->steuernUndAbgaben = $latestInputForSteuernUndAbgaben;
        $this->moneySheetSteuernUndAbgabenForm->isSteuernUndAbgabenInputDisabled = $latestInputForSteuernUndAbgaben === $calculatedSteuernUndAbgaben;

        $latestInputForLebenshaltungskosten = MoneySheetState::getLastInputForLebenshaltungskosten($this->gameStream, $this->myself);
        $calculatedLebenshaltungskosten = MoneySheetState::calculateLebenshaltungskostenForPlayer($this->gameStream,
            $this->myself);
        $this->moneySheetLebenshaltungskostenForm->lebenshaltungskosten = $latestInputForLebenshaltungskosten;
        $this->moneySheetLebenshaltungskostenForm->isLebenshaltungskostenInputDisabled = $latestInputForLebenshaltungskosten === $calculatedLebenshaltungskosten;
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

    public function setLebenshaltungskosten(): void
    {
        $this->moneySheetLebenshaltungskostenForm->validate();
        $this->coreGameLogic->handle($this->gameId, EnterLebenshaltungskostenForPlayer::create($this->myself,
            $this->moneySheetLebenshaltungskostenForm->lebenshaltungskosten));

        $updatedEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $resultOfLastInput = MoneySheetState::getResultOfLastLebenshaltungskostenInput($updatedEvents, $this->myself);

        if (!$resultOfLastInput->wasSuccessful && $resultOfLastInput->fine > 0) {
            $this->moneySheetLebenshaltungskostenForm->addError('lebenshaltungskosten',
                "Du hast einen falschen Wert für die Lebenshaltungskosten eingegeben. Dir wurden $resultOfLastInput->fine € abgezogen. Wir haben den Wert für dich korrigiert.");
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
        $this->coreGameLogic->handle($this->gameId, EnterSteuernUndAbgabenForPlayer::create($this->myself,
            $this->moneySheetSteuernUndAbgabenForm->steuernUndAbgaben));

        $updatedEvents = $this->coreGameLogic->getGameEvents($this->gameId);
        $resultOfLastInput = MoneySheetState::getResultOfLastSteuernUndAbgabenInput($updatedEvents, $this->myself);

        if (!$resultOfLastInput->wasSuccessful && $resultOfLastInput->fine > 0) {
            $this->moneySheetSteuernUndAbgabenForm->addError('steuernUndAbgaben',
                "Du hast einen falschen Wert für die Steuern und Abgaben eingegeben. Dir wurden $resultOfLastInput->fine € abgezogen. Wir haben den Wert für dich korrigiert.");
            $this->moneySheetSteuernUndAbgabenForm->isSteuernUndAbgabenInputDisabled = true;
        } elseif (!$resultOfLastInput->wasSuccessful) {
            $this->moneySheetSteuernUndAbgabenForm->addError('steuernUndAbgaben',
                "Du hast einen falschen Wert für die Steuern und Abgaben eingegeben.");
        }

        $this->broadcastNotify();
    }

}
