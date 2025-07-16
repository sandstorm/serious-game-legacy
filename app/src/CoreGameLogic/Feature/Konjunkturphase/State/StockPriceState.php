<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\StockPrice;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\ProvidesStockPriceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphaseTypeEnum;
use Random\RandomException;

class StockPriceState
{
    /**
     * @param GameEvents $gameEvents
     * @return StockPrice[]
     * @throws RandomException
     */
    public static function calculateStockPrices(GameEvents $gameEvents): array
    {
        // This method should calculate the stock prices based on the current game state.
        // For now, we return an empty array as a placeholder.
        return [
            self::calculateStockPrice(StockType::LOW_RISK, $gameEvents),
            self::calculateStockPrice(StockType::HIGH_RISK, $gameEvents),
        ];
    }

    public static function getCurrentStockPrice(GameEvents $gameEvents, StockType $stockType): MoneyAmount
    {
        // get last event that provides stock price changes
        $lastEvent = $gameEvents->findLastOrNull(ProvidesStockPriceChanges::class);

        if ($lastEvent === null) {
            // If no stock prices are available, return the initial stock price.
            return new MoneyAmount(Configuration::INITIAL_STOCK_PRICE);
        }

        return $lastEvent->getStockPrice($stockType);
    }

    /**
     * calculates the stock based on this formula: S(t+1) = St*e(μ−0,5*σ2)+σ*Z*(1 + s)
     *
     * @param StockType $stockType
     * @param GameEvents $gameEvents
     * @return StockPrice
     * @throws \Random\RandomException
     */
    private static function calculateStockPrice(StockType $stockType, GameEvents $gameEvents): StockPrice
    {
        $currentStockPrice = self::getCurrentStockPrice($gameEvents, $stockType);
        if (!GamePhaseState::hasKonjunkturphase($gameEvents)) {
            return new StockPrice($stockType, $currentStockPrice);
        }

        $konjunkturphaseDefinition = KonjunkturphaseState::getCurrentKonjunkturphase($gameEvents);
        $schock = self::getSchock($konjunkturphaseDefinition->type);
        $annualVolatility = self::getAnnualVolatility($stockType);
        $annualReturn = self::getAnnualReturn($stockType);
        $z = random_int(-1000, 1000) / 1000.0; // Random number from N(0,1), here simplified as a uniform distribution

        $growth = exp($annualReturn - 0.5 * $annualVolatility ** 2) + $annualVolatility * $z;
        $newPrice = $currentStockPrice->value * $growth * (1 + $schock);

        return new StockPrice($stockType, new MoneyAmount($newPrice));
    }

    /**
     * Returns the annual volatility (σ) based on the stock type.
     *
     * @param StockType $stockType
     * @return float
     */
    private static function getAnnualVolatility(StockType $stockType): float
    {
        return match ($stockType) {
            StockType::LOW_RISK => 0.15,
            StockType::HIGH_RISK => 0.4,
        };
    }

    /**
     * Returns the annual return (μ) based on the stock type.
     *
     * @param StockType $stockType
     * @return float
     */
    private static function getAnnualReturn(StockType $stockType): float
    {
        return match ($stockType) {
            StockType::LOW_RISK => 0.07,
            StockType::HIGH_RISK => 0.09,
        };
    }

    /**
     * Returns the shock (s) based on the current economic phase.
     *
     * @param KonjunkturphaseTypeEnum $type
     * @return float
     */
    private static function getSchock(KonjunkturphaseTypeEnum $type): float
    {
        return match ($type) {
            KonjunkturphaseTypeEnum::AUFSCHWUNG => 0.05,
            KonjunkturphaseTypeEnum::BOOM => 0.15,
            KonjunkturphaseTypeEnum::REZESSION => -0.2,
            KonjunkturphaseTypeEnum::DEPRESSION => -0.1,
        };
    }
}
