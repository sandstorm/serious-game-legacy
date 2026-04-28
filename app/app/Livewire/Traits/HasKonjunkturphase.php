<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\ValueObject\NotificationTypeEnum;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\CompleteMoneySheetForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\MarkPlayerAsReadyForKonjunkturphaseChangeAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\StartKonjunkturphaseForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\CompleteMoneysheetForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\MarkPlayerAsReadyForKonjunkturphaseChange;
use Domain\CoreGameLogic\Feature\Spielzug\Command\StartKonjunkturphaseForPlayer;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Illuminate\View\View;

trait HasKonjunkturphase
{
    public string|null $summaryActiveTabId = null;
    // shows/hides details modal
    public bool $konjunkturphaseDetailsVisible = false;
    public int $konjunkturphaseStartScreenPage = 0;
    public ?int $lastSeenKonjunkturphaseId = null;

    public function renderingHasKonjunkturphase(): void
    {
        if (PreGameState::isInPreGamePhase($this->getGameEvents())) {
            return;
        }
        $currentId = GamePhaseState::currentKonjunkturphasenId($this->getGameEvents())->value;
        if ($this->lastSeenKonjunkturphaseId !== null && $this->lastSeenKonjunkturphaseId !== $currentId) {
            // Phase rolled over — drop stale modal state from the previous phase so it does
            // not bleed into the new one (see issue #652). Auto-derived flags such as
            // showItsYourTurnNotification, isFinishedGameModalVisible, sellInvestmentsModalIsVisible
            // and playerHasToPlayCard are intentionally NOT reset here — their own
            // renderingHas*() hooks recompute them every render from the event store.
            $this->resetModalFlagsOnPhaseChange();
        }
        $this->lastSeenKonjunkturphaseId = $currentId;
    }

    private function resetModalFlagsOnPhaseChange(): void
    {
        // HasKonjunkturphase
        $this->konjunkturphaseDetailsVisible = false;
        $this->konjunkturphaseStartScreenPage = 0;
        $this->summaryActiveTabId = null;

        // HasMoneySheet — most likely to be open at phase end since the moneysheet IS the
        // phase-end UI flow.
        $this->moneySheetIsVisible = false;
        $this->editIncomeIsVisible = false;
        $this->editExpensesIsVisible = false;
        $this->takeOutALoanIsVisible = false;
        $this->repaymentFormForLoanId = null;

        // HasCard
        $this->showCardActionsForCard = null;
        $this->isEreignisCardVisible = false;
        $this->ereignisCardDefinition = null;

        // HasInvestitionen / HasInsolvenz (sellInvestmentOfType is declared in both traits;
        // PHP merges identical declarations so we set it once).
        $this->showInvestitionenSelelectionModal = false;
        $this->showStocksModal = false;
        $this->showETFModal = false;
        $this->showCryptoModal = false;
        $this->showImmobilienModal = false;
        $this->isBuyImmobilieVisible = false;
        $this->isSellImmobilieVisible = false;
        $this->buyInvestmentOfType = null;
        $this->sellInvestmentOfType = null;

        // HasInsolvenz
        $this->isSellInvestmentsToAvoidInsolvenzModalVisible = false;
        $this->isShowInformationForFiledInsolvenzModalVisible = false;
        $this->isSellImmobilienToAvoidInsolvenzModalVisible = false;

        // Other single-modal traits
        $this->jobOfferIsVisible = false;
        $this->isMinijobVisible = false;
        $this->isWeiterbildungVisible = false;
        $this->isChangeLebenszielphaseVisible = false;
        $this->showLebenszielForPlayer = null;
    }

    public function renderKonjunkturphaseStartScreen(): View
    {
        return view('livewire.screens.konjunkturphase-start', [
            'konjunkturphase' => KonjunkturphaseState::getCurrentKonjunkturphase($this->getGameEvents()),
            'previousKonjunkturphase' => KonjunkturphaseState::getPreviousKonjunkturphaseOrNull($this->getGameEvents()),
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

    public function canMarkPlayerAsReady(): AktionValidationResult
    {
        $aktion = new MarkPlayerAsReadyForKonjunkturphaseChangeAktion();
        return $aktion->validate($this->myself, $this->getGameEvents());
    }

    public function markPlayerAsReady(): void
    {
        $validationResult = self::canMarkPlayerAsReady();
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
