<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\Forms\MoneySheetLebenshaltungskostenForm;
use App\Livewire\Forms\MoneySheetSteuernUndAbgabenForm;
use Domain\CoreGameLogic\Feature\Moneysheet\Command\EnterLebenshaltungskostenForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\Command\EnterSteuernUndAbgabenForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\LebenshaltungskostenForPlayerWereCorrected;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\SteuernUndAbgabenForPlayerWereCorrected;
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
     * Prefixed with "mount" to avoid conflicts with Livewire's mount method.
     * Is automatically called by Livewire.
     * See https://livewire.laravel.com/docs/lifecycle-hooks#using-hooks-inside-a-trait
     *
     * @return void
     */
    public function mountHasMoneySheet(): void
    {
        $calculatedSteuernUndAbgaben = MoneySheetState::calculateSteuernUndAbgabenForPlayer($this->gameStream, $this->myself);
        $actualEnteredSteuernUndAbgaben = MoneySheetState::getLastEnteredSteuernUndAbgabenForPlayer($this->gameStream, $this->myself);

        if ($actualEnteredSteuernUndAbgaben !== null && $calculatedSteuernUndAbgaben !== $actualEnteredSteuernUndAbgaben) {
            $this->moneySheetSteuernUndAbgabenForm->addError('steuern und abgaben',
                'Deine Steuern und Abgaben sind aktuell nicht korrekt eingegeben.');
        }

        $this->moneySheetSteuernUndAbgabenForm->isSteuernUndAbgabenInputDisabled = $actualEnteredSteuernUndAbgaben === $calculatedSteuernUndAbgaben;
        $this->moneySheetSteuernUndAbgabenForm->steuernUndAbgaben = $actualEnteredSteuernUndAbgaben !== null ? $actualEnteredSteuernUndAbgaben : 0;

        $calculatedLebenshaltungskosten = MoneySheetState::calculateLebenshaltungskostenForPlayer($this->gameStream, $this->myself);
        $actualEnteredLebenshaltungskosten = MoneySheetState::getLastEnteredLebenshaltungskostenForPlayer($this->gameStream, $this->myself);

        if ($actualEnteredLebenshaltungskosten !== null && $calculatedLebenshaltungskosten !== $actualEnteredLebenshaltungskosten) {
            $this->moneySheetLebenshaltungskostenForm->addError('lebenshaltungskosten',
                'Deine Lebenshaltungskosten sind aktuell nicht korrekt eingegeben.');
        }

        $this->moneySheetLebenshaltungskostenForm->lebenshaltungskostenIsDisabled = $actualEnteredLebenshaltungskosten === $calculatedLebenshaltungskosten;
        $this->moneySheetLebenshaltungskostenForm->lebenshaltungskosten = $actualEnteredLebenshaltungskosten !== null ? $actualEnteredLebenshaltungskosten : 0;
    }

    /**
     * Update the form state on a rendering. Can happen for example when user changes their job.
     * Rerendering is triggered by the Livewire when we use the broadcastNotify() method.
     *
     * @return void
     */
    public function renderingHasMoneySheet(): void
    {
        $calculatedSteuernUndAbgaben = MoneySheetState::calculateSteuernUndAbgabenForPlayer($this->gameStream, $this->myself);
        $actualEnteredSteuernUndAbgaben = MoneySheetState::getLastEnteredSteuernUndAbgabenForPlayer($this->gameStream, $this->myself);

        if ($calculatedSteuernUndAbgaben !== $this->moneySheetSteuernUndAbgabenForm->steuernUndAbgaben) {
            $event = new SteuernUndAbgabenForPlayerWereCorrected($this->myself, $calculatedSteuernUndAbgaben);
            $fine = $event->getResourceChanges($this->myself);
            $this->moneySheetSteuernUndAbgabenForm->addError('steuern und abgaben',
                "Du hast einen falschen Wert für die Lebenshaltungskosten eingegeben. Es wurden dir $fine € abgezogen.");
        }

        $this->moneySheetSteuernUndAbgabenForm->isSteuernUndAbgabenInputDisabled = $actualEnteredSteuernUndAbgaben === $calculatedSteuernUndAbgaben;

        $calculatedLebenshaltungskosten = MoneySheetState::calculateLebenshaltungskostenForPlayer($this->gameStream, $this->myself);
        $actualEnteredLebenshaltungskosten = MoneySheetState::getLastEnteredLebenshaltungskostenForPlayer($this->gameStream, $this->myself);

        // show error message when user made a mistake in the input
        // is done in the rerendering phase, because the event fired on the input change handles the 250 EUR deduction
        if ($calculatedLebenshaltungskosten !== $this->moneySheetLebenshaltungskostenForm->lebenshaltungskosten) {
            $fine = LebenshaltungskostenForPlayerWereCorrected::getFineForPlayer();
            $this->moneySheetLebenshaltungskostenForm->addError('lebenshaltungskosten',
                "Du hast einen falschen Wert für die Lebenshaltungskosten eingegeben. Es wurden dir $fine € abgezogen.");
        }

        $this->moneySheetLebenshaltungskostenForm->lebenshaltungskostenIsDisabled = $actualEnteredLebenshaltungskosten === $calculatedLebenshaltungskosten;
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
        $actualEnteredLebenshaltungskosten = MoneySheetState::getLastEnteredLebenshaltungskostenForPlayer($this->gameStream, $this->myself);
        if ($actualEnteredLebenshaltungskosten !== null && $this->moneySheetLebenshaltungskostenForm->lebenshaltungskosten === $actualEnteredLebenshaltungskosten) {
            return; // no change, nothing to do
        }

        $this->moneySheetLebenshaltungskostenForm->validate();
        $this->coreGameLogic->handle($this->gameId, EnterLebenshaltungskostenForPlayer::create($this->myself, $this->moneySheetLebenshaltungskostenForm->lebenshaltungskosten));
        $this->broadcastNotify();
    }

    public function setSteuernUndAbgaben(): void
    {
        $actualEnteredSteuernUndAbgaben = MoneySheetState::getLastEnteredSteuernUndAbgabenForPlayer($this->gameStream, $this->myself);
        if ($actualEnteredSteuernUndAbgaben !== null && $this->moneySheetSteuernUndAbgabenForm->steuernUndAbgaben === $actualEnteredSteuernUndAbgaben) {
            return; // no change, nothing to do
        }

        $this->moneySheetSteuernUndAbgabenForm->validate();
        $this->coreGameLogic->handle($this->gameId, EnterSteuernUndAbgabenForPlayer::create($this->myself, $this->moneySheetSteuernUndAbgabenForm->steuernUndAbgaben));
        $this->broadcastNotify();
    }

}
