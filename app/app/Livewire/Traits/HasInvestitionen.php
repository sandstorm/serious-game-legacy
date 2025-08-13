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
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\SellInvestmentsForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\BuyInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DontSellInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SellInvestmentsForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\InvestmentsWereBoughtForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Investments\ValueObject\InvestmentId;

trait HasInvestitionen
{
    public bool $showInvestitionenSelelectionModal = false;
    public bool $showStocksModal = false;
    public bool $showETFModal = false;
    public bool $showCryptoModal = false;
    public ?InvestmentId $buyInvestmentOfType = null;
    public BuyInvestmentsForm $buyInvestmentsForm;
    public SellInvestmentsForm $sellInvestmentsForm;
    public bool $sellStocksModalIsVisible = false;

    /**
     * Update the form state on a rendering. Can happen for example when user changes their job.
     * Rerendering is triggered by Livewire when we use the broadcastNotify() method.
     *
     * @return void
     */
    public function renderingHasInvestitionen(): void
    {
        if (PreGameState::isInPreGamePhase($this->gameEvents)) {
            // do not mount the if we are in pre-game phase
            return;
        }

        $this->sellStocksModalIsVisible = false;
        if (GamePhaseState::anotherPlayerHasBoughtInvestmentsThisTurn($this->gameEvents, $this->myself) &&
            !PlayerState::hasPlayerInteractedWithInvestmentsModalThisTurn($this->gameEvents, $this->myself)) {

            $stocksBoughtEvent = $this->gameEvents->findLast(InvestmentsWereBoughtForPlayer::class);
            $this->sellInvestmentsForm->stockType = $stocksBoughtEvent->investmentId;
            $this->sellInvestmentsForm->sharePrice = InvestmentPriceState::getCurrentInvestmentPrice($this->gameEvents, $stocksBoughtEvent->investmentId)->value;
            $this->sellInvestmentsForm->amountOwned = PlayerState::getAmountOfAllInvestmentsOfTypeForPlayer(
                $this->gameEvents,
                $this->myself,
                $stocksBoughtEvent->investmentId
            );

            $this->sellStocksModalIsVisible = true;
        }
    }

    public function toggleInvestitionenSelectionModal(): void
    {
        $this->showInvestitionenSelelectionModal = !$this->showInvestitionenSelelectionModal;
    }

    public function toggleStocksModal(): void
    {
        $this->showInvestitionenSelelectionModal = false;
        if ($this->buyInvestmentOfType !== null) {
            $this->buyInvestmentOfType = null;
            return;
        }

        $this->showStocksModal = !$this->showStocksModal;
    }

    public function toggleETFModal(): void
    {
        $this->showInvestitionenSelelectionModal = false;
        if ($this->buyInvestmentOfType !== null) {
            $this->buyInvestmentOfType = null;
            return;
        }
        $this->showETFModal = !$this->showETFModal;
    }

    public function toggleCryptoModal(): void
    {
        $this->showInvestitionenSelelectionModal = false;
        if ($this->buyInvestmentOfType !== null) {
            $this->buyInvestmentOfType = null;
            return;
        }
        $this->showCryptoModal = !$this->showCryptoModal;
    }

    public function canBuyInvestments(InvestmentId $investmentId): AktionValidationResult
    {
        $aktion = new BuyInvestmentsForPlayerAktion(
            $investmentId,
            InvestmentPriceState::getCurrentInvestmentPrice($this->gameEvents, $investmentId),
            $this->buyInvestmentsForm->amount
        );
        return $aktion->validate($this->myself, $this->gameEvents);
    }

    public function showbuyInvestmentOfType(string $investmentId): void
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
        $this->buyInvestmentsForm->guthaben = PlayerState::getGuthabenForPlayer($this->gameEvents, $this->myself)->value;
        $this->buyInvestmentsForm->sharePrice = InvestmentPriceState::getCurrentInvestmentPrice($this->gameEvents, InvestmentId::from($investmentId))->value;
    }

    public function buyInvestments(string $investmentId): void
    {
        $this->buyInvestmentsForm->validate();
        $investmentId = InvestmentId::from($investmentId);
        if (!$this->canBuyInvestments($investmentId)->canExecute) {
            return;
        }

        $this->coreGameLogic->handle($this->gameId, BuyInvestmentsForPlayer::create(
            $this->myself,
            $investmentId,
            $this->buyInvestmentsForm->amount
        ));

        $this->toggleStocksModal();
        $this->showNotification(
            $this->buyInvestmentsForm->amount . ' * ' . $investmentId->value . ' wurde erfolgreich gekauft. Alle anderen Spieler:innen haben jetzt die Möglichkeit ihre Investition zu verkaufen.',
            NotificationTypeEnum::INFO
        );
        $this->broadcastNotify();
    }

    public function closeSellStocksModal(): void
    {
        $stocksBoughtEvent = $this->gameEvents->findLast(InvestmentsWereBoughtForPlayer::class);
        $this->coreGameLogic->handle($this->gameId, DontSellInvestmentsForPlayer::create(
            $this->myself,
            $stocksBoughtEvent->investmentId
        ));
        $this->broadcastNotify();
        $this->sellStocksModalIsVisible = false;
    }

    public function canSellStocks(InvestmentId $investmentId): AktionValidationResult
    {
        $aktion = new SellInvestmentsForPlayerAktion(
            $investmentId,
            InvestmentPriceState::getCurrentInvestmentPrice($this->gameEvents, $investmentId),
            $this->sellInvestmentsForm->amount
        );
        return $aktion->validate($this->myself, $this->gameEvents);
    }

    public function sellStocks(string $investmentId): void
    {
        $this->sellInvestmentsForm->validate();
        $investmentId = InvestmentId::from($investmentId);

        $validationResult = self::canSellStocks($investmentId);
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            return;
        }

        $this->coreGameLogic->handle($this->gameId, SellInvestmentsForPlayer::create(
            $this->myself,
            $investmentId,
            $this->sellInvestmentsForm->amount
        ));

        $this->sellStocksModalIsVisible = false;
        $this->sellInvestmentsForm->reset();
        $this->sellInvestmentsForm->resetValidation();
        $this->showNotification(
            $this->sellInvestmentsForm->amount . ' * ' . $investmentId->value . ' wurdee erfolgreich verkauft.',
            NotificationTypeEnum::INFO
        );
        $this->broadcastNotify();
    }
}
