<?php

declare(strict_types=1);

namespace App\View\Components\MoneySheet\Income;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\StockPriceState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\StockData;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Illuminate\View\Component;
use Illuminate\View\View;

class MoneySheetInvestments extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        public PlayerId $playerId,
        public GameEvents $gameEvents,
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        $stocks = [];
        // loop over StockType enum values and get sum and amount
        foreach (StockType::cases() as $stockType) {
            $amount = PlayerState::getAmountOfAllStocksOfTypeForPlayer($this->gameEvents, $this->playerId, $stockType);
            if ($amount === 0) {
                continue; // skip if no stocks of this type
            }
            $currentPrice = StockPriceState::getCurrentStockPrice($this->gameEvents, $stockType);
            $stocks[$stockType->value] = new StockData(
                stockType: $stockType,
                price: $currentPrice,
                amount: $amount,
                totalValue: new MoneyAmount($currentPrice->value * $amount),
                totalDividend: new MoneyAmount(
                    $stockType->value === StockType::LOW_RISK->value
                        ? $amount * KonjunkturphaseState::getCurrentKonjunkturphase($this->gameEvents)->getDividend()->value
                        : 0
                ),
            );
        }
        return view('components.gameboard.moneySheet.income.money-sheet-investments', [
            'stocks' => $stocks
        ]);
    }

}
