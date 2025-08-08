<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\ProvidesImmobilienPriceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\ImmobilieId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\ImmobilienCardDefinition;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

class ImmobilienPriceState
{
    /**
     * @param GameEvents $gameEvents
     * @param ImmobilieId $immobilieId
     * @return MoneyAmount
     */
    public static function getCurrentPriceForImmobilie(GameEvents $gameEvents, ImmobilieId $immobilieId): MoneyAmount
    {
        // get last event that provides investment price changes
        $lastEvent = $gameEvents->findLastOrNull(ProvidesImmobilienPriceChanges::class);
        $immobilienDefinition = CardFinder::getInstance()->getCardById($immobilieId->cardId, ImmobilienCardDefinition::class);


        if ($lastEvent === null) {
            // If no price changes are available, return the initial investment price.
            return $immobilienDefinition->getPurchasePrice();
        }

        $immobilienPrices = $lastEvent->getImmobilienPrices();
        foreach ($immobilienPrices as $immobilienPrice) {
            if ($immobilienPrice->immobilieId->equals($immobilieId)) {
                return $immobilienPrice->price;
            }
        }

        return $immobilienDefinition->getPurchasePrice();
    }
}
