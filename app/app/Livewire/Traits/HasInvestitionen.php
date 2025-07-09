<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\Forms\BuyStocksForm;
use App\Livewire\ValueObject\InvestitionenTabEnum;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\BuyStocksForPlayerAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\BuyStocksForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

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

    public function canBuyStocks(): AktionValidationResult
    {
        $aktion = new BuyStocksForPlayerAktion(
            StockType::LOW_RISK,
            new MoneyAmount($this->buyLowRiskStocksForm->price),
            $this->buyLowRiskStocksForm->amount
        );
        return $aktion->validate($this->myself, $this->gameEvents);
    }

    public function buyLowRiskStocks(): void
    {
        if (!$this->canBuyStocks()->canExecute) {
            return;
        }

        $this->buyLowRiskStocksForm->guthaben = PlayerState::getGuthabenForPlayer($this->gameEvents, $this->myself)->value;
        $this->buyLowRiskStocksForm->price = $this->getLowRiskStocksPrice()->value;
        $this->buyLowRiskStocksForm->validate();

        $this->coreGameLogic->handle($this->gameId, BuyStocksForPlayer::create(
            $this->myself,
            StockType::LOW_RISK,
            new MoneyAmount($this->buyLowRiskStocksForm->price),
            $this->buyLowRiskStocksForm->amount
        ));

        // TODO success message
        $this->broadcastNotify();
    }

    public function buyHighRiskStocks(): void
    {
        if (!$this->canBuyStocks()->canExecute) {
            return;
        }

        $this->buyLowRiskStocksForm->guthaben = PlayerState::getGuthabenForPlayer($this->gameEvents, $this->myself)->value;
        $this->buyLowRiskStocksForm->price = $this->getHighRiskStocksPrice()->value;
        $this->buyHighRiskStocksForm->validate();

        $this->coreGameLogic->handle($this->gameId, BuyStocksForPlayer::create(
            $this->myself,
            StockType::HIGH_RISK,
            new MoneyAmount($this->buyHighRiskStocksForm->price),
            $this->buyLowRiskStocksForm->amount
        ));

        // TODO success message
        $this->broadcastNotify();
    }

    public function getLowRiskStocksPrice(): MoneyAmount
    {
        return new MoneyAmount(40); // Example price for low-risk stocks
    }

    public function getHighRiskStocksPrice(): MoneyAmount
    {
        return new MoneyAmount(50); // Example price for high-risk stocks
    }
}
