<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\ImmobilienPrice;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\ProvidesImmobilienPriceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\Dto\InvestitionenCardDefinition;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Konjunkturphase\ValueObject\AuswirkungScopeEnum;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphaseTypeEnum;
use Random\RandomException;

class ImmobilienPriceState
{
    /**
     * Get the current prices for all immoblien owned by players.
     *
     * @param GameEvents $gameEvents
     * @param KonjunkturphaseTypeEnum $type
     * @return ImmobilienPrice[]
     * @throws RandomException
     */
    public static function calculateImmobilienPrices(GameEvents $gameEvents, KonjunkturphaseTypeEnum $type): array
    {
        // get all immoblien bought
        $players = GamePhaseState::getOrderedPlayers($gameEvents);

        /** @var InvestitionenCardDefinition[] $immobilien */
        $immobilien = [];
        foreach ($players as $player) {
            $immobilien = array_merge($immobilien, PlayerState::getImmoblienOwnedByPlayer($gameEvents, $player));
        }

        $prices = [];
        foreach ($immobilien as $immobilie) {
            $prices[] = new ImmobilienPrice(
                $immobilie->getId(),
                self::calculatePriceForImmobilie($immobilie->getPurchasePrice(), $type)
            );
        }

        return $prices;
    }

    /**
     * @param GameEvents $gameEvents
     * @param InvestitionenCardDefinition $immobilie
     * @return MoneyAmount
     */
    public static function getCurrentImmobiliePrice(GameEvents $gameEvents, InvestitionenCardDefinition $immobilie): MoneyAmount
    {
        // get last event that provides investment price changes
        $lastEvent = $gameEvents->findLastOrNull(ProvidesImmobilienPriceChanges::class);

        if ($lastEvent === null) {
            // If no price changes are available, return the initial investment price.
            return $immobilie->getPurchasePrice();
        }

        $immobilienPrices = $lastEvent->getImmobilienPrices();
        foreach ($immobilienPrices as $immobilienPrice) {
            if ($immobilienPrice->cardId === $immobilie->getId()) {
                return $immobilienPrice->price;
            }
        }

        return $immobilie->getPurchasePrice();
    }

    /**
     * @param MoneyAmount $purchasePrice
     * @param KonjunkturphaseTypeEnum $type
     * @return MoneyAmount
     * @throws RandomException
     */
    public static function calculatePriceForImmobilie(MoneyAmount $purchasePrice, KonjunkturphaseTypeEnum $type): MoneyAmount
    {
        $aufschwungFactor = self::getAufschwungFactor($type);
        return new MoneyAmount($purchasePrice->value * $aufschwungFactor);
    }

    /**
     * @param KonjunkturphaseTypeEnum $type
     * @return float
     * @throws RandomException
     */
    private static function getAufschwungFactor(KonjunkturphaseTypeEnum $type): float
    {
        $randomNumber = random_int(0, 100) / 100;

        // get random jitter between -0.05 and +0.05
        $jitter = (random_int(-5, 5)) / 100;


        $kBase = match ($type) {
            KonjunkturphaseTypeEnum::BOOM => 0.9 + $randomNumber * (1.2 - 0.9),
            KonjunkturphaseTypeEnum::AUFSCHWUNG => 0.88 + $randomNumber * (1.15 - 0.88),
            KonjunkturphaseTypeEnum::REZESSION => 0.87 + $randomNumber * (1.07 - 0.87),
            KonjunkturphaseTypeEnum::DEPRESSION => 0.85 + $randomNumber * (0.97 - 0.85),
        };


        $kFinal = self::clamp($kBase + $jitter, 0.85, 1.25);
        return $kFinal;
    }

    /**
     * @param float $value
     * @param float $min
     * @param float $max
     * @return float
     */
    private static function clamp(float $value, float $min, float $max): float
    {
        if ($value < $min) {
            return $min;
        }
        if ($value > $max) {
            return $max;
        }
        return $value;
    }
}
