<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\CompleteMoneysheetForPlayer;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\StartKonjunkturphaseForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Illuminate\View\View;

trait HasKonjunkturphase
{
    public string|null $summaryActiveTabId = null;
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
        if (MoneySheetState::hasPlayerCompletedMoneysheet($this->gameEvents, $this->myself)) {
            if ($this->summaryActiveTabId === null) {
                $this->summaryActiveTabId = $this->myself->value;
            }
            return view('livewire.screens.konjunkturphaseSummary', [
                'summaryActiveTabId' => $this->summaryActiveTabId,
            ]);
        }
        return view('livewire.screens.konjunkturphaseEnding', [
        ]);
    }

    public function showMoneysheetSummaryForPlayer(string $playerId): void
    {
        $this->summaryActiveTabId = $playerId;
        $this->broadcastNotify();
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

    public function completeMoneysheetForPlayer(): void
    {
        $this->coreGameLogic->handle($this->gameId, CompleteMoneysheetForPlayer::create($this->myself));
        $this->broadcastNotify();
    }
}
