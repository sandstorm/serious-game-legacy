<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Helper;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\ImmobilienPrice;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasBoughtImmobilie;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\ImmobilienCardDefinition;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphaseTypeEnum;
use Random\RandomException;

class ImmobilienPriceHelper
{
    /**
     * Calculates new prices for all immobilien owned by players. This function is
     * non-deterministic and will return a different result everytime it is called
     * due to a random factor in the calculation.
     *
     * @param GameEvents $gameEvents
     * @param KonjunkturphaseTypeEnum $type
     * @return ImmobilienPrice[]
     * @throws RandomException
     */
    public static function calculateNewPricesForImmobilienOwnedByPlayers(GameEvents $gameEvents, KonjunkturphaseTypeEnum $type): array
    {
        $players = GamePhaseState::getOrderedPlayers($gameEvents);

        /** @var PlayerHasBoughtImmobilie[] $immobilien */
        $immobilien = [];
        foreach ($players as $player) {
            $immobilien = array_merge($immobilien, PlayerState::getImmoblienOwnedByPlayer($gameEvents, $player));
        }

        $prices = [];
        foreach ($immobilien as $immobilie) {
            $immobilienDefinition = CardFinder::getInstance()->getCardById($immobilie->getCardId(), ImmobilienCardDefinition::class);
            $prices[] = new ImmobilienPrice(
                $immobilie->getImmobilieId(),
                self::calculateNewPriceForImmobilie($immobilienDefinition->getPurchasePrice(), $type)
            );
        }

        return $prices;
    }

    /**
     * @internal public for testing only
     * @param MoneyAmount $purchasePrice
     * @param KonjunkturphaseTypeEnum $type
     * @return MoneyAmount
     * @throws RandomException
     */
    public static function calculateNewPriceForImmobilie(MoneyAmount $purchasePrice, KonjunkturphaseTypeEnum $type): MoneyAmount
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
