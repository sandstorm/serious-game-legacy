<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

class MoneySheet
{
    public function __construct(
        public float $lebenshaltungskosten,
        public bool  $doesLebenshaltungskostenRequirePlayerAction,
        public float $steuernUndAbgaben,
        public bool  $doesSteuernUndAbgabenRequirePlayerAction,
        public float $gehalt,
        public float $totalInsuranceCost
    ) {}
}
