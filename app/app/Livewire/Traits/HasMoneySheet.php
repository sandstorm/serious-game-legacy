<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

trait HasMoneySheet
{
    public bool $moneySheetIsVisible = false;
    public bool $editIncomeIsVisible = false;
    public bool $editExpensesIsVisible = false;

    // set in the view money-sheet-income.blade.php
    public string $activeTabForIncome = 'investments'; // 'investments', 'salary'
    // set in the view money-sheet-expenses.blade.php
    public string $activeTabForExpenses = 'credits'; // 'credits', 'kids', 'insurances', 'taxes', 'livingCosts'

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
}
