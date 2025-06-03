<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

trait HasMoneySheet
{
    public bool $moneySheetIsVisible = false;

    public function showMoneySheet(): void
    {
        $this->moneySheetIsVisible = true;
    }

    public function closeMoneySheet(): void
    {
        $this->moneySheetIsVisible = false;
    }
}
