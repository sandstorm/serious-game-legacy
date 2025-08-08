<?php

declare(strict_types=1);

namespace App\View\Components\MoneySheet\Income;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\InvestmentPriceState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\InvestmentData;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
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
        $investments = [];
        foreach (InvestmentId::cases() as $investmentId) {
            $amount = PlayerState::getAmountOfAllInvestmentsOfTypeForPlayer($this->gameEvents, $this->playerId, $investmentId);
            if ($amount === 0) {
                continue; // skip if no investments of this type exist
            }
            $currentPrice = InvestmentPriceState::getCurrentInvestmentPrice($this->gameEvents, $investmentId);
            $investments[$investmentId->value] = new InvestmentData(
                investmentId: $investmentId,
                price: $currentPrice,
                amount: $amount,
                totalValue: new MoneyAmount($currentPrice->value * $amount),
                totalDividend: new MoneyAmount(
                    $investmentId->value === InvestmentId::MERFEDES_PENZ->value
                        ? $amount * KonjunkturphaseState::getCurrentKonjunkturphase($this->gameEvents)->getDividend()->value
                        : 0
                ),
            );
        }

        return view('components.gameboard.moneySheet.income.money-sheet-investments', [
            'investments' => $investments,
            'immobilien' => PlayerState::getImmoblienOwnedByPlayer($this->gameEvents, $this->playerId),
        ]);
    }

}
