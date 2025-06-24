<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use Illuminate\View\View;

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


    public function renderKonjunkturphaseEndScreen(): View
    {
        return view('livewire.screens.konjunkturphaseEnding', [
        ]);
    }
}
