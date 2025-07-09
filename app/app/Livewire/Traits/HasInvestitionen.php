<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\Forms\BuyStocksForm;
use App\Livewire\ValueObject\InvestitionenTabEnum;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
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

    public function buyLowRiskStocks(): void
    {
        $this->buyLowRiskStocksForm->guthaben = PlayerState::getGuthabenForPlayer($this->gameEvents, $this->myself)->value;
        $this->buyLowRiskStocksForm->price = $this->getLowRiskStocksPrice()->value;
        $this->buyLowRiskStocksForm->validate();
    }

    public function buyHighRiskStocks(): void
    {
        $this->buyLowRiskStocksForm->guthaben = PlayerState::getGuthabenForPlayer($this->gameEvents, $this->myself)->value;
        $this->buyLowRiskStocksForm->price = $this->getHighRiskStocksPrice()->value;
        $this->buyHighRiskStocksForm->validate();
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
