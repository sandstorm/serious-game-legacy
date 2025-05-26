<?php

declare(strict_types=1);

namespace Domain\Definitions\Cards;

use Domain\Definitions\Cards\ValueObject\CardId;
use Domain\Definitions\Cards\ValueObject\PileId;

/**
 * TODO this is just a placeholder until we have a mechanism to organize our cards in piles (DB/files/?)
 */
readonly final class PileFinder
{

    /**
     * @param PileId $pileId
     * @return CardId[]
     */
    public static function getCardsIdsForPile(PileId $pileId): array
    {
        // WHY array_values: we want to reindex the array
        return array_values(array_map(fn ($card) => $card->id, CardFinder::getCardsForPile($pileId)));
    }

}
