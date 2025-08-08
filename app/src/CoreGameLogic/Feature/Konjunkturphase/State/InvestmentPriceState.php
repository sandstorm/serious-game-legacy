<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\InvestmentPrice;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\ProvidesInvestmentPriceChanges;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;

class InvestmentPriceState
{
    /**
     * @param GameEvents $gameEvents
     * @return InvestmentPrice[]
     */
    public static function getCurrentInvestmentPrices(GameEvents $gameEvents): array
    {
        // get last event that provides investment price changes
        $lastEvent = $gameEvents->findLastOrNull(ProvidesInvestmentPriceChanges::class);

        if ($lastEvent === null) {
            // If no investment price changes are available, return the initial investment prices.
            return [
                new InvestmentPrice(InvestmentId::MERFEDES_PENZ, new MoneyAmount(Configuration::INITIAL_INVESTMENT_PRICE)),
                new InvestmentPrice(InvestmentId::BETA_PEAR, new MoneyAmount(Configuration::INITIAL_INVESTMENT_PRICE)),
                new InvestmentPrice(InvestmentId::ETF_MSCI_WORLD, new MoneyAmount(Configuration::INITIAL_INVESTMENT_PRICE)),
                new InvestmentPrice(InvestmentId::ETF_CLEAN_ENERGY, new MoneyAmount(Configuration::INITIAL_INVESTMENT_PRICE)),
                new InvestmentPrice(InvestmentId::BAT_COIN, new MoneyAmount(Configuration::INITIAL_INVESTMENT_PRICE)),
                new InvestmentPrice(InvestmentId::MEME_COIN, new MoneyAmount(Configuration::INITIAL_INVESTMENT_PRICE)),
            ];
        }

        return $lastEvent->getInvestmentPrices();
    }

    /**
     * @param GameEvents $gameEvents
     * @param InvestmentId $investmentType
     * @return MoneyAmount
     */
    public static function getCurrentInvestmentPrice(GameEvents $gameEvents, InvestmentId $investmentType): MoneyAmount
    {
        // get last event that provides investment price changes
        $lastEvent = $gameEvents->findLastOrNull(ProvidesInvestmentPriceChanges::class);

        if ($lastEvent === null) {
            // If no investment price changes are available, return the initial investment price.
            return new MoneyAmount(Configuration::INITIAL_INVESTMENT_PRICE);
        }

        $investmentPrices = $lastEvent->getInvestmentPrices();
        foreach ($investmentPrices as $investmentPrice) {
            if ($investmentPrice->investmentId === $investmentType) {
                return $investmentPrice->price;
            }
        }

        throw new \RuntimeException("Investment price for {$investmentType->value} not found.");
    }
}
