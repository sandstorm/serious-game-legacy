<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\StartKonjunkturphaseForPlayer;
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

    public function renderKonjunkturphaseStartScreen(): View
    {
        return view('livewire.screens.konjunkturphaseStart', [
        ]);
    }

    public function startKonjunkturphaseForPlayer(): void
    {
        $this->coreGameLogic->handle($this->gameId, StartKonjunkturphaseForPlayer::create($this->myself));
        $this->broadcastNotify();
    }
}
