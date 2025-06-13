<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

class MoneySheet
{
    public function __construct(
        public float $lebenshaltungskosten,
        public float $steuernUndAbgaben,
        public float $gehalt,
    ) {}
}
