<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\GameReport\Dto;

use Domain\Definitions\Investments\ValueObject\InvestmentId;

/**
 * The prices of all investments over the whole game and the trades of all players.
 * Prices only change at the start of a year and at the end of a Spielzug in which a player traded.
 */
final readonly class InvestmentPriceHistory
{
    /**
     * @param array<string, list<InvestmentPricePoint>> $pricesByInvestment keyed by investment id
     * @param array<string, list<InvestmentTrade>> $tradesByInvestment keyed by investment id
     */
    public function __construct(
        private array $pricesByInvestment,
        private array $tradesByInvestment,
        private int $numberOfSpielzuege,
    ) {
    }

    /**
     * @return list<InvestmentPricePoint>
     */
    public function getPricesFor(InvestmentId $investmentId): array
    {
        return $this->pricesByInvestment[$investmentId->value] ?? [];
    }

    /**
     * @return list<InvestmentTrade>
     */
    public function getTradesFor(InvestmentId $investmentId): array
    {
        return $this->tradesByInvestment[$investmentId->value] ?? [];
    }

    public function getNumberOfSpielzuege(): int
    {
        return $this->numberOfSpielzuege;
    }
}
