<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\Forms\BuyStocksForm;
use App\Livewire\Forms\SellStocksForm;
use App\Livewire\ValueObject\NotificationTypeEnum;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\StockPriceState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\BuyStocksForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\SellStocksForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\BuyStocksForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\DontSellStocksForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SellStocksForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\StocksWereBoughtForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;

trait HasInvestitionen
{
    public bool $showInvestitionenSelelectionModal = false;
    public bool $showStocksModal = false;
    public ?StockType $buyStocksOfType = null;
    public BuyStocksForm $buyStocksForm;
    public SellStocksForm $sellStocksForm;
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
        if (GamePhaseState::anotherPlayerHasBoughtStocksThisTurn($this->gameEvents, $this->myself) &&
            !PlayerState::hasPlayerInteractedWithStocksModalThisTurn($this->gameEvents, $this->myself)) {

            $stocksBoughtEvent = $this->gameEvents->findLast(StocksWereBoughtForPlayer::class);
            $this->sellStocksForm->stockType = $stocksBoughtEvent->stockType;
            $this->sellStocksForm->sharePrice = StockPriceState::getCurrentStockPrice($this->gameEvents, $stocksBoughtEvent->stockType)->value;
            $this->sellStocksForm->amountOwned = PlayerState::getAmountOfAllStocksOfTypeForPlayer(
                $this->gameEvents,
                $this->myself,
                $stocksBoughtEvent->stockType
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
        if ($this->buyStocksOfType !== null) {
            $this->buyStocksOfType = null;
            return;
        }

        $this->showStocksModal = !$this->showStocksModal;
    }

    public function canBuyStocks(StockType $stockType): AktionValidationResult
    {
        $aktion = new BuyStocksForPlayerAktion(
            $stockType,
            StockPriceState::getCurrentStockPrice($this->gameEvents, $stockType),
            $this->buyStocksForm->amount
        );
        return $aktion->validate($this->myself, $this->gameEvents);
    }

    public function showBuyStocksOfType(string $stockType): void
    {
        $validationResult = self::canBuyStocks(StockType::from($stockType));
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            return;
        }

        $this->buyStocksOfType = StockType::from($stockType);
        $this->buyStocksForm->guthaben = PlayerState::getGuthabenForPlayer($this->gameEvents, $this->myself)->value;
        $this->buyStocksForm->sharePrice = StockPriceState::getCurrentStockPrice($this->gameEvents, StockType::from($stockType))->value;
    }

    public function buyStocks(string $stockType): void
    {
        $this->buyStocksForm->validate();
        $stockType = StockType::from($stockType);
        if (!$this->canBuyStocks($stockType)->canExecute) {
            return;
        }

        $this->coreGameLogic->handle($this->gameId, BuyStocksForPlayer::create(
            $this->myself,
            $stockType,
            $this->buyStocksForm->amount
        ));

        $this->toggleStocksModal();
        $this->showNotification(
            'Aktien wurden erfolgreich gekauft. Alle anderen Spieler:innen haben jetzt die Möglichkeit ihre Aktien verkaufen.',
            NotificationTypeEnum::INFO
        );
        $this->broadcastNotify();
    }

    public function closeSellStocksModal(): void
    {
        $this->coreGameLogic->handle($this->gameId, DontSellStocksForPlayer::create(
            $this->myself,
            $this->sellStocksForm->stockType
        ));
        $this->broadcastNotify();
        $this->sellStocksModalIsVisible = false;
    }

    public function canSellStocks(StockType $stockType): AktionValidationResult
    {
        $aktion = new SellStocksForPlayerAktion(
            $stockType,
            StockPriceState::getCurrentStockPrice($this->gameEvents, $stockType),
            $this->sellStocksForm->amount
        );
        return $aktion->validate($this->myself, $this->gameEvents);
    }

    public function sellStocks(string $stockType): void
    {
        $this->sellStocksForm->validate();
        $stockType = StockType::from($stockType);

        $validationResult = self::canSellStocks($stockType);
        if (!$validationResult->canExecute) {
            $this->showNotification(
                $validationResult->reason,
                NotificationTypeEnum::ERROR
            );
            return;
        }

        $this->coreGameLogic->handle($this->gameId, SellStocksForPlayer::create(
            $this->myself,
            $stockType,
            $this->sellStocksForm->amount
        ));

        $this->sellStocksModalIsVisible = false;
        $this->showNotification(
            'Aktien wurden erfolgreich verkauft.',
            NotificationTypeEnum::INFO
        );
        $this->broadcastNotify();
    }
}
