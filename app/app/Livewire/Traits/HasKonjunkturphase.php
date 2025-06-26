<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\StartKonjunkturphaseForPlayer;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Illuminate\View\View;

trait HasKonjunkturphase
{
    // shows/hides details modal
    public bool $konjunkturphaseDetailsVisible = false;
    public int $konjunkturphaseStartScreenPage = 0;

    public function renderKonjunkturphaseEndScreen(): View
    {
        return view('livewire.screens.konjunkturphase-ending', [
        ]);
    }

    public function renderKonjunkturphaseStartScreen(): View
    {
        return view('livewire.screens.konjunkturphase-start', [
            'konjunkturphase' => KonjunkturphaseState::getCurrentKonjunkturphase($this->gameEvents),
            'currentPage' => $this->konjunkturphaseStartScreenPage,
        ]);
    }

    public function showKonjunkturphaseDetails(): void
    {
        $this->konjunkturphaseDetailsVisible = true;
    }

    public function closeKonjunkturphaseDetails(): void
    {
        $this->konjunkturphaseDetailsVisible = false;
    }

    public function nextKonjunkturphaseStartScreenPage(): void
    {
        $this->konjunkturphaseStartScreenPage++;
    }

    public function startKonjunkturphaseForPlayer(): void
    {
        $this->coreGameLogic->handle($this->gameId, StartKonjunkturphaseForPlayer::create($this->myself));
        $this->broadcastNotify();
    }
}
