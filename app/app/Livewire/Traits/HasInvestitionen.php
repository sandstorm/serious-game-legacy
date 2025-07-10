<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\Forms\BuyStocksForm;
use App\Livewire\ValueObject\InvestitionenTabEnum;
use App\Livewire\ValueObject\NotificationTypeEnum;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\StockPriceState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\BuyStocksForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\BuyStocksForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;

trait HasInvestitionen
{
    public bool $showInvestitionen = false;
    public InvestitionenTabEnum $investitionenActiveTab = InvestitionenTabEnum::STOCKS;
    public BuyStocksForm $buyLowRiskStocksForm;
    public BuyStocksForm $buyHighRiskStocksForm;

    public function toggleInvestitionen(): void
    {
        $this->showInvestitionen = !$this->showInvestitionen;
    }

    public function canBuyStocks(StockType $stockType): AktionValidationResult
    {
        $aktion = new BuyStocksForPlayerAktion(
            $stockType,
            StockPriceState::getCurrentStockPrice($this->gameEvents, $stockType),
            $stockType === StockType::LOW_RISK
                ? $this->buyLowRiskStocksForm->amount
                : $this->buyHighRiskStocksForm->amount,
        );
        return $aktion->validate($this->myself, $this->gameEvents);
    }

    public function buyLowRiskStocks(): void
    {
        if (!$this->canBuyStocks(StockType::LOW_RISK)->canExecute) {
            return;
        }

        $this->buyLowRiskStocksForm->guthaben = PlayerState::getGuthabenForPlayer($this->gameEvents, $this->myself)->value;
        $this->buyLowRiskStocksForm->sharePrice = StockPriceState::getCurrentStockPrice($this->gameEvents, StockType::LOW_RISK)->value;
        $this->buyLowRiskStocksForm->validate();

        $this->coreGameLogic->handle($this->gameId, BuyStocksForPlayer::create(
            $this->myself,
            StockType::LOW_RISK,
            $this->buyLowRiskStocksForm->amount
        ));

        $this->toggleInvestitionen();
        $this->showNotification(
            'Aktien wurden erfolgreich gekauft.',
            NotificationTypeEnum::INFO
        );
        $this->broadcastNotify();
    }

    public function buyHighRiskStocks(): void
    {
        if (!$this->canBuyStocks(StockType::HIGH_RISK)->canExecute) {
            return;
        }

        $this->buyHighRiskStocksForm->guthaben = PlayerState::getGuthabenForPlayer($this->gameEvents, $this->myself)->value;
        $this->buyHighRiskStocksForm->sharePrice = StockPriceState::getCurrentStockPrice($this->gameEvents, StockType::HIGH_RISK)->value;
        $this->buyHighRiskStocksForm->validate();

        $this->coreGameLogic->handle($this->gameId, BuyStocksForPlayer::create(
            $this->myself,
            StockType::HIGH_RISK,
            $this->buyHighRiskStocksForm->amount
        ));

        $this->toggleInvestitionen();
        $this->showNotification(
            'Aktien wurden erfolgreich gekauft.',
            NotificationTypeEnum::INFO
        );
        $this->broadcastNotify();
    }
}
