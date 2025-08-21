<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\InvestmentPrice;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\ProvidesInvestmentPriceChanges;
use Domain\Definitions\Investments\InvestmentFinder;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Konjunkturphase\ValueObject\AuswirkungScopeEnum;
use Random\RandomException;

class InvestmentPriceState
{
    /**
     * @param GameEvents $gameEvents
     * @return InvestmentPrice[]
     * @throws RandomException
     */
    public static function calculateInvestmentPrices(GameEvents $gameEvents): array
    {
        // This method should calculate the investment prices based on the current game state.
        return [
            self::calculatePriceGTMWithJumpDiffusion(InvestmentId::MERFEDES_PENZ, $gameEvents),
            self::calculatePriceGTMWithJumpDiffusion(InvestmentId::BETA_PEAR, $gameEvents),
            self::calculatePriceGTMWithJumpDiffusion(InvestmentId::ETF_MSCI_WORLD, $gameEvents),
            self::calculatePriceGTMWithJumpDiffusion(InvestmentId::ETF_CLEAN_ENERGY, $gameEvents),
            self::calculatePriceGTMWithJumpDiffusion(InvestmentId::BAT_COIN, $gameEvents),
            self::calculatePriceGTMWithJumpDiffusion(InvestmentId::MEME_COIN, $gameEvents),
        ];
    }

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

    /**
     * Get new price based on Geometrische Brownsche Bewegung (GBM) model with Jump Diffusion.
     *
     * @param InvestmentId $investmentType
     * @param GameEvents $gameEvents
     * @return InvestmentPrice
     * @throws RandomException
     */
    public static function calculatePriceGTMWithJumpDiffusion(
        InvestmentId $investmentType,
        GameEvents   $gameEvents
    ): InvestmentPrice {
        $currentInvestmentPrice = self::getCurrentInvestmentPrice($gameEvents, $investmentType);
        if (!GamePhaseState::hasKonjunkturphase($gameEvents)) {
            return new InvestmentPrice($investmentType, $currentInvestmentPrice);
        }

        $volatility = self::getVolatility($investmentType);
        $drift = self::getDrift($investmentType);
        $poissonRate = self::getJumpsPerYear($investmentType);
        $jumpSize = self::getJumpSize($investmentType);
        $jumpControl = self::getJumpControl($investmentType);
        $schock = self::getSchock($gameEvents, $investmentType);
        $randomNumber = random_int(-1000, 1000) / 1000.0; // Random number from N(-1,1), here simplified as a uniform distribution

        $driftTerm = $drift - 0.5 * $volatility ** 2 - $poissonRate * $jumpSize;
        $diffusion = $volatility * $randomNumber;

        $jumps = self::poisson($poissonRate);
        $jumpSum = 0;
        for ($i = 1; $i <= $jumps; $i++) {
            $yi = $jumpSize + ($jumpControl * $randomNumber); // Random jump size
            $jumpSum += $jumpSize + $yi;
        }

        $exponent = $driftTerm + $diffusion + $jumpSum + $schock;

        $newPrice = $currentInvestmentPrice->value * exp($exponent);
        $lowerBound = 1;
        $upperBound = $currentInvestmentPrice->value * 3;
        if ($newPrice < $lowerBound) {
            $newPrice = $lowerBound;
        } elseif ($newPrice > $upperBound) {
            $newPrice = $upperBound;
        }
        return new InvestmentPrice($investmentType, new MoneyAmount($newPrice));
    }

    /**
     * Volatility (σ / sigma) - Returns the annual volatility based on the investment type.
     *
     * @param InvestmentId $investmentId
     * @return float
     */
    private static function getVolatility(InvestmentId $investmentId): float
    {
        return InvestmentFinder::findInvestmentById($investmentId)->fluctuations / 100;
    }

    /**
     * Drift (μ / mu) - Returns the annual return based on the investment type.
     *
     * @param InvestmentId $investmentId
     * @return float
     */
    private static function getDrift(InvestmentId $investmentId): float
    {
        return InvestmentFinder::findInvestmentById($investmentId)->longTermTrend / 100;
    }

    /**
     * Jumps per year (λ / lambda) - Returns the Poisson rate based on the investment type.
     *
     * @param InvestmentId $investmentId
     * @return float
     */
    private static function getJumpsPerYear(InvestmentId $investmentId): float
    {
        return InvestmentFinder::findInvestmentById($investmentId)->jumpPerYear;
    }

    /**
     * Jump size (k̄ / kbar) - Returns the jump size based on the investment type.
     *
     * @param InvestmentId $investmentId
     * @return float
     */
    private static function getJumpSize(InvestmentId $investmentId): float
    {
        return InvestmentFinder::findInvestmentById($investmentId)->jumpSize / 100;
    }

    /**
     * Jump control (δ / delta) - Returns the jump control based on the investment type.
     *
     * @param InvestmentId $investmentId
     * @return float
     */
    private static function getJumpControl(InvestmentId $investmentId): float
    {
        return InvestmentFinder::findInvestmentById($investmentId)->jumpControl / 100;
    }

    /**
     * Returns the shock (s) based on the current economic phase.
     *
     * @param GameEvents $gameEvents
     * @param InvestmentId $investmentType
     * @return float
     */
    private static function getSchock(GameEvents $gameEvents, InvestmentId $investmentType): float
    {
        $konjunkturphaseDefinition = KonjunkturphaseState::getCurrentKonjunkturphase($gameEvents);

        $schockStocks = $konjunkturphaseDefinition->getAuswirkungByScope(AuswirkungScopeEnum::STOCKS_BONUS)->value / 100;
        $schockCrypto = $konjunkturphaseDefinition->getAuswirkungByScope(AuswirkungScopeEnum::CRYPTO)->value / 100;

        return match ($investmentType) {
            InvestmentId::MERFEDES_PENZ, InvestmentId::BETA_PEAR => $schockStocks,
            InvestmentId::ETF_MSCI_WORLD, InvestmentId::ETF_CLEAN_ENERGY => 0,
            InvestmentId::BAT_COIN, InvestmentId::MEME_COIN => $schockCrypto,
        };
    }

    private static function poisson(float $lampda): float
    {
        $L = exp(-$lampda);
        $k = 0;
        $p = 1.0;
        do {
            $k++;
            $p *= random_int(0, 1000) / 1000.0; // Simulating rng.nextDouble() with a uniform distribution
        } while ($p > $L);
        return $k - 1; // Return k - 1 as per the original logic
    }
}
