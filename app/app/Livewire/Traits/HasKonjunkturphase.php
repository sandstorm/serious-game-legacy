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
            'konjunkturphase' => KonjunkturphaseState::getCurrentKonjunkturphase($this->getGameEvents()),
            'currentPage' => $this->konjunkturphaseStartScreenPage,
        ]);
    }

    public function renderKonjunkturphaseEndScreen(): View
    {
        if (MoneySheetState::hasPlayerCompletedMoneysheet($this->getGameEvents(), $this->myself)) {
            if ($this->summaryActiveTabId === null) {
                $this->summaryActiveTabId = $this->myself->value;
            }
        }
        return view('livewire.screens.konjunkturphase-over');
    }

    public function showKonjunkturphaseDetails(): void
    {
        $this->konjunkturphaseDetailsVisible = true;
    }

    public function closeKonjunkturphaseDetails(): void
    {
        $this->konjunkturphaseDetailsVisible = false;
    }

    public function prevKonjunkturphaseStartScreenPage(): void
    {
        $this->konjunkturphaseStartScreenPage--;
    }

    public function nextKonjunkturphaseStartScreenPage(): void
    {
        $this->konjunkturphaseStartScreenPage++;
    }

    public function showMoneysheetSummaryForPlayer(string $playerId): void
    {
        $this->summaryActiveTabId = $playerId;
        $this->broadcastNotify();
    }

    public function startKonjunkturphaseForPlayer(): void
    {
        $aktion = new StartKonjunkturphaseForPlayerAktion();
        $validationResult = $aktion->validate($this->myself, $this->getGameEvents());
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            return;
        }
        $this->handleCommand(StartKonjunkturphaseForPlayer::create($this->myself));
        $this->broadcastNotify();
        $this->konjunkturphaseStartScreenPage = 0;
    }

    public function completeMoneysheetForPlayer(): void
    {
        $aktion = new CompleteMoneySheetForPlayerAktion();
        $validationResult = $aktion->validate($this->myself, $this->getGameEvents());
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            return;
        }
        $this->handleCommand(CompleteMoneysheetForPlayer::create($this->myself));
        $this->moneySheetIsVisible = false;
        $this->broadcastNotify();
    }

    public function canCompleteMoneysheet(): bool
    {
        $aktion = new CompleteMoneySheetForPlayerAktion();
        $validationResult = $aktion->validate($this->myself, $this->getGameEvents());
        return $validationResult->canExecute;
    }

    public function markPlayerAsReady(): void
    {
        $aktion = new MarkPlayerAsReadyForKonjunkturphaseChangeAktion();
        $validationResult = $aktion->validate($this->myself, $this->getGameEvents());
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            return;
        }
        $this->handleCommand(MarkPlayerAsReadyForKonjunkturphaseChange::create($this->myself));
        $this->broadcastNotify();
    }
}
