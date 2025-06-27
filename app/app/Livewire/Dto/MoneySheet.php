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
        public float $gehalt,
        public float $total,
        public float $totalInsuranceCost,
        public MoneyAmount $sumOfAllLoans
    ) {}
}
