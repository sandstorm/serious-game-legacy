<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

trait HasKonjunkturphase
{
    public bool $konjunkturphaseDetailsVisible = false;

    public function showKonjunkturphaseDetails(): void
    {
        $this->konjunkturphaseDetailsVisible = true;
    }

    public function closeKonjunkturphaseDetails(): void
    {
        $this->konjunkturphaseDetailsVisible = false;
    }
}
