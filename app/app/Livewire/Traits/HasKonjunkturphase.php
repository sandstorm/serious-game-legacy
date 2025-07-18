<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\ValueObject\NotificationTypeEnum;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\CompleteMoneySheetForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\MarkPlayerAsReadyForKonjunkturphaseChangeAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\StartKonjunkturphaseForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CompleteMoneysheetForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\MarkPlayerAsReadyForKonjunkturphaseChange;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartKonjunkturphaseForPlayer;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Illuminate\View\View;

trait HasKonjunkturphase
{
    public string|null $summaryActiveTabId = null;
    // shows/hides details modal
    public bool $konjunkturphaseDetailsVisible = false;
    public int $konjunkturphaseStartScreenPage = 0;

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

    public function renderKonjunkturphaseEndScreen(): View
    {
        if (MoneySheetState::hasPlayerCompletedMoneysheet($this->gameEvents, $this->myself)) {
            if ($this->summaryActiveTabId === null) {
                $this->summaryActiveTabId = $this->myself->value;
            }
            return view('livewire.screens.konjunkturphase-summary', [
                'summaryActiveTabId' => $this->summaryActiveTabId,
            ]);
        }
        return view('livewire.screens.konjunkturphase-ending', [
        ]);
    }

    public function showMoneysheetSummaryForPlayer(string $playerId): void
    {
        $this->summaryActiveTabId = $playerId;
        $this->broadcastNotify();
    }

    public function startKonjunkturphaseForPlayer(): void
    {
        $aktion = new StartKonjunkturphaseForPlayerAktion();
        $validationResult = $aktion->validate($this->myself, $this->gameEvents);
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            $this->broadcastNotify();
            return;
        }
        $this->coreGameLogic->handle($this->gameId, StartKonjunkturphaseForPlayer::create($this->myself));
        $this->broadcastNotify();
    }

    public function completeMoneysheetForPlayer(): void
    {
        $aktion = new CompleteMoneySheetForPlayerAktion();
        $validationResult = $aktion->validate($this->myself, $this->gameEvents);
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            $this->broadcastNotify();
            return;
        }
        $this->coreGameLogic->handle($this->gameId, CompleteMoneysheetForPlayer::create($this->myself));
        $this->broadcastNotify();
    }

    public function canCompleteMoneysheet(): bool
    {
        $aktion = new CompleteMoneySheetForPlayerAktion();
        $validationResult = $aktion->validate($this->myself, $this->gameEvents);
        return $validationResult->canExecute;
    }

    public function markPlayerAsReady(): void
    {
        $aktion = new MarkPlayerAsReadyForKonjunkturphaseChangeAktion();
        $validationResult = $aktion->validate($this->myself, $this->gameEvents);
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            $this->broadcastNotify();
            return;
        }
        $this->coreGameLogic->handle($this->gameId, MarkPlayerAsReadyForKonjunkturphaseChange::create($this->myself));
        $this->broadcastNotify();
    }
}
