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
        public bool  $doesSteuernUndAbgabenRequirePlayerAction,
        public MoneyAmount $gehalt,
        public MoneyAmount $total,
        public MoneyAmount $totalInsuranceCost,
        public MoneyAmount $sumOfAllLoans
    ) {}
}
