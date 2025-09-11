<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

use Domain\Definitions\Card\ValueObject\MoneyAmount;

class MoneySheet
{
    public function __construct(
        public MoneyAmount $lebenshaltungskosten,
        public bool  $doesLebenshaltungskostenRequirePlayerAction,
        public MoneyAmount $steuernUndAbgaben,
        public bool        $doesSteuernUndAbgabenRequirePlayerAction,
        public MoneyAmount $gehalt,
        public MoneyAMount $totalFromPlayerInput,
        public MoneyAmount $totalInsuranceCost,
        public MoneyAmount $annualExpensesForAllLoans,
        public MoneyAmount $sumOfAllAssets,
        public MoneyAmount $annualIncome,
        public MoneyAmount $annualExpenses,
        public MoneyAMount $annualExpensesFromPlayerInput,
        public MoneyAmount $guthabenBeforeKonjunkturphaseChange,
        public MoneyAmount $guthabenAfterKonjunkturphaseChange,
        public MoneyAmount $insolvenzabgaben,
    ) {}
}
