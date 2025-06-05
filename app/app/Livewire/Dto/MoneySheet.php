<?php

declare(strict_types=1);

namespace App\Livewire\Dto;

class MoneySheet
{
    public function __construct(
        public int $lebenskosten,
        public int $gehalt,
    ) {}
}
