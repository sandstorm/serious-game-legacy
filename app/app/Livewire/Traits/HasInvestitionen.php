<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\Forms\BuyInvestmentsForm;
use App\Livewire\Forms\SellInvestmentsForm;
use App\Livewire\ValueObject\NotificationTypeEnum;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\InvestmentPriceState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\BuyInvestmentsForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\SellInvestmentsForPlayerAfterInvestmentByAnotherPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\SellInvestmentsForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\BuyInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DontSellInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SellInvestmentsForPlayerAfterInvestmentByAnotherPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SellInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\InvestmentsWereBoughtForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Event\InvestmentsWereSoldForPlayerAfterInvestmentByAnotherPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Event\InvestmentsWereSoldForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Investments\ValueObject\InvestmentId;

trait HasInvestitionen
{
    public bool $showInvestitionenSelelectionModal = false;
    public bool $showStocksModal = false;
    public bool $showETFModal = false;
    public bool $showCryptoModal = false;
    public ?InvestmentId $buyInvestmentOfType = null;
    public ?InvestmentId $sellInvestmentOfType = null;
    public BuyInvestmentsForm $buyInvestmentsForm;
    public SellInvestmentsForm $sellInvestmentsForm;
    public bool $sellInvestmentsModalIsVisible = false;

    /**
     * Update the form state on a rendering. Can happen for example when user changes their job.
     * Rerendering is triggered by Livewire when we use the broadcastNotify() method.
     *
     * @return void
     */
    public function renderingHasInvestitionen(): void
    {
        if (PreGameState::isInPreGamePhase($this->getGameEvents())) {
            // do not mount the if we are in pre-game phase
            return;
        }

        $this->sellInvestmentsModalIsVisible = false;
        if (GamePhaseState::anotherPlayerHasInvestedThisTurn($this->getGameEvents(), $this->myself) &&
            !PlayerState::hasPlayerInteractedWithInvestmentsModalThisTurn($this->getGameEvents(), $this->myself)) {

            $investmentsBoughtEvent = $this->getGameEvents()->findLast(InvestmentsWereBoughtForPlayer::class);
            $this->sellInvestmentsForm->playerName = PlayerState::getNameForPlayer($this->getGameEvents(), $investmentsBoughtEvent->playerId);
            $this->sellInvestmentsForm->investmentId = $investmentsBoughtEvent->investmentId;
            $this->sellInvestmentsForm->sharePrice = InvestmentPriceState::getCurrentInvestmentPrice($this->getGameEvents(), $investmentsBoughtEvent->investmentId)->value;
            $this->sellInvestmentsForm->amountOwned = PlayerState::getAmountOfAllInvestmentsOfTypeForPlayer(
                $this->getGameEvents(),
                $this->myself,
                $investmentsBoughtEvent->investmentId
            );

            $this->sellInvestmentsModalIsVisible = true;
        }
    }

    public function toggleInvestitionenSelectionModal(): void
    {
        $this->showInvestitionenSelelectionModal = !$this->showInvestitionenSelelectionModal;
    }

    public function toggleStocksModal(): void
    {
        $this->showInvestitionenSelelectionModal = false;
        if ($this->buyInvestmentOfType !== null || $this->sellInvestmentOfType !== null) {
            $this->buyInvestmentOfType = null;
            $this->sellInvestmentOfType = null;
            return;
        }

        $this->showStocksModal = !$this->showStocksModal;
    }

    public function toggleETFModal(): void
    {
        $this->showInvestitionenSelelectionModal = false;
        if ($this->buyInvestmentOfType !== null || $this->sellInvestmentOfType !== null) {
            $this->buyInvestmentOfType = null;
            $this->sellInvestmentOfType = null;
            return;
        }
        $this->showETFModal = !$this->showETFModal;
    }

    public function toggleCryptoModal(): void
    {
        $this->showInvestitionenSelelectionModal = false;
        if ($this->buyInvestmentOfType !== null || $this->sellInvestmentOfType !== null) {
            $this->buyInvestmentOfType = null;
            $this->sellInvestmentOfType = null;
            return;
        }
        $this->showCryptoModal = !$this->showCryptoModal;
    }

    public function closeInvestmentModals(): void
    {
        $this->showStocksModal = false;
        $this->showCryptoModal = false;
        $this->showETFModal = false;
        $this->buyInvestmentOfType = null;
        $this->sellInvestmentOfType = null;
    }

    public function canBuyInvestments(InvestmentId $investmentId): AktionValidationResult
    {
        $aktion = new BuyInvestmentsForPlayerAktion(
            $investmentId,
            InvestmentPriceState::getCurrentInvestmentPrice($this->getGameEvents(), $investmentId),
            $this->buyInvestmentsForm->amount ?? 0
        );
        return $aktion->validate($this->myself, $this->getGameEvents());
    }

    public function showBuyInvestmentOfType(string $investmentId): void
    {
        $this->buyInvestmentsForm->reset();
        $this->buyInvestmentsForm->resetValidation();

        $validationResult = self::canBuyInvestments(InvestmentId::from($investmentId));
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            return;
        }

        $this->buyInvestmentOfType = InvestmentId::from($investmentId);
        $this->buyInvestmentsForm->guthaben = PlayerState::getGuthabenForPlayer($this->getGameEvents(), $this->myself)->value;
        $this->buyInvestmentsForm->sharePrice = InvestmentPriceState::getCurrentInvestmentPrice($this->getGameEvents(), InvestmentId::from($investmentId))->value;
    }

    public function buyInvestments(string $investmentId): void
    {
        $this->buyInvestmentsForm->validate();
        $investmentId = InvestmentId::from($investmentId);
        $validationResult = self::canBuyInvestments($investmentId);
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            return;
        }

        // Amount should not ever be null, but just in case and to fix phpstan errors
        if ($this->buyInvestmentsForm->amount === null) {
            return;
        }

        $this->handleCommand(BuyInvestmentsForPlayer::create(
            $this->myself,
            $investmentId,
            $this->buyInvestmentsForm->amount
        ));

        $this->closeInvestmentModals();
        $this->broadcastNotify();

        /** @var InvestmentsWereBoughtForPlayer $event */
        $event = $this->getGameEvents()->findLast(InvestmentsWereBoughtForPlayer::class);
        $this->showBanner($event->amount . ' Anteile von ' . $investmentId->value . ' wurden erfolgreich gekauft. Alle anderen Spieler:innen haben jetzt die Möglichkeit ihre Anteile zu verkaufen.', $event->getResourceChanges($this->myself));
    }

    public function closeSellInvestmentsModal(): void
    {
        $stocksBoughtEvent = $this->getGameEvents()->findLast(InvestmentsWereBoughtForPlayer::class);
        $this->handleCommand(DontSellInvestmentsForPlayer::create(
            $this->myself,
            $stocksBoughtEvent->investmentId
        ));
        $this->broadcastNotify();
        $this->sellInvestmentsModalIsVisible = false;
    }

    public function canSellInvestmentsAfterPurchase(InvestmentId $investmentId): AktionValidationResult
    {
        $aktion = new SellInvestmentsForPlayerAfterInvestmentByAnotherPlayerAktion(
            $investmentId,
            InvestmentPriceState::getCurrentInvestmentPrice($this->getGameEvents(), $investmentId),
            $this->sellInvestmentsForm->amount ?? 0
        );
        return $aktion->validate($this->myself, $this->getGameEvents());
    }

    public function sellInvestmentsAfterPurchase(string $investmentId): void
    {
        $this->sellInvestmentsForm->validate();
        $investmentId = InvestmentId::from($investmentId);

        $validationResult = self::canSellInvestmentsAfterPurchase($investmentId);
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            return;
        }

        // Amount should not ever be null, but just in case and to fix phpstan errors
        if ($this->sellInvestmentsForm->amount === null) {
            return;
        }

        $this->handleCommand(SellInvestmentsForPlayerAfterInvestmentByAnotherPlayer::create(
            $this->myself,
            $investmentId,
            $this->sellInvestmentsForm->amount
        ));

        $this->sellInvestmentsModalIsVisible = false;
        $this->sellInvestmentsForm->reset();
        $this->sellInvestmentsForm->resetValidation();
        $this->broadcastNotify();

        /** @var InvestmentsWereSoldForPlayerAfterInvestmentByAnotherPlayer $event */
        $event = $this->getGameEvents()->findLast(InvestmentsWereSoldForPlayerAfterInvestmentByAnotherPlayer::class);
        $this->showBanner($event->amount . ' Anteile von ' . $investmentId->value . ' wurden erfolgreich verkauft.', $event->getResourceChanges($this->myself));
    }

    public function canSellInvestments(InvestmentId $investmentId): AktionValidationResult
    {
        $aktion = new SellInvestmentsForPlayerAktion(
            $investmentId,
            InvestmentPriceState::getCurrentInvestmentPrice($this->getGameEvents(), $investmentId),
            $this->sellInvestmentsForm->amount ?? 0
        );
        return $aktion->validate($this->myself, $this->getGameEvents());
    }

    public function showSellInvestmentOfType(string $investmentId): void
    {
        $this->sellInvestmentsForm->reset();
        $this->sellInvestmentsForm->resetValidation();

        $investmentId = InvestmentId::from($investmentId);

        $validationResult = self::canSellInvestments($investmentId);
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            return;
        }

        $this->sellInvestmentOfType = $investmentId;
        $this->sellInvestmentsForm->investmentId = $investmentId;
        $this->sellInvestmentsForm->sharePrice = InvestmentPriceState::getCurrentInvestmentPrice($this->getGameEvents(), $investmentId)->value;
        $this->sellInvestmentsForm->amountOwned = PlayerState::getAmountOfAllInvestmentsOfTypeForPlayer(
            $this->getGameEvents(),
            $this->myself,
            $investmentId
        );
    }

    public function sellInvestments(string $investmentId): void
    {
        $this->sellInvestmentsForm->validate();
        $investmentId = InvestmentId::from($investmentId);

        $validationResult = self::canSellInvestments($investmentId);
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            return;
        }

        // Amount should not ever be null, but just in case and to fix phpstan errors
        if ($this->sellInvestmentsForm->amount === null) {
            return;
        }

        $this->handleCommand(SellInvestmentsForPlayer::create(
            $this->myself,
            $investmentId,
            $this->sellInvestmentsForm->amount
        ));

        $this->closeInvestmentModals();
        $this->broadcastNotify();

        /** @var InvestmentsWereSoldForPlayer $event */
        $event = $this->getGameEvents()->findLast(InvestmentsWereSoldForPlayer::class);
        $this->showBanner($event->amount . ' Anteile von ' . $investmentId->value . ' wurden erfolgreich verkauft. Alle anderen Spieler:innen haben jetzt die Möglichkeit ihre Anteile zu verkaufen', $event->getResourceChanges($this->myself));
    }
}
