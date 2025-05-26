<?php

declare(strict_types=1);

namespace Domain\Definitions\Card;

use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileEnum;
use Domain\Definitions\Card\ValueObject\PileId;

/**
 * TODO this is just a placeholder until we have a mechanism to organize our cards in piles (DB/files/?)
 */
readonly final class PileFinder
{

    /**
     * @param PileEnum $pileId
     * @return CardId[]
     */
    public static function getCardsIdsForPile(PileEnum $pileId): array
    {
        // WHY array_values: we want to reindex the array
        return array_values(array_map(fn ($card) => $card->id, CardFinder::getCardsForPile($pileId)));
    }

}
