<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

trait HasKonjunkturphase
{
    public bool $konjunkturzyklusDetailsVisible = false;

    public function showKonjunkturzyklusDetails(): void
    {
        $this->konjunkturzyklusDetailsVisible = true;
    }

    public function closeKonjunkturzyklusDetails(): void
    {
        $this->konjunkturzyklusDetailsVisible = false;
    }
}
