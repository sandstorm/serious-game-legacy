<?php

declare(strict_types=1);

namespace Domain\Definitions\Pile;

use Domain\CoreGameLogic\Dto\ValueObject\CardId;

/**
 * TODO this is just a placeholder until we have a mechanism to organize our cards in piles (DB/files/?)
 */
readonly final class PileFinder
{
    /**
     * @return CardId[]
     */
    public static function getCardsForBildungAndKarriere(): array
    {
        return [
            new CardId('sprachkurs'),
            new CardId('Gedächtnistraining'),
            new CardId('Erste Hilfe Kurs'),
        ];
    }

    /**
     * @return CardId[]
     */
    public static function getCardsForSozialesAndFreizeit(): array
    {
        return [
            new CardId('neues Hobby'),
            new CardId('Fitness'),
            new CardId('Volleyballverein'),
            new CardId('Soziales Engagement'),
        ];
    }

    /**
     * @return CardId[]
     */
    public static function getCardsForErwerbseinkommen(): array
    {
        return [
            new CardId('Fachinformatikerin'),
            new CardId('Pflegefachkraft'),
            new CardId('Taxifahrerin'),
        ];
    }

    /**
     * @return CardId[]
     */
    public static function getCardsForInvestition(): array
    {
        return [
            new CardId('Skandal - GreenEnergy (AGE)'),
            new CardId('Anstieg - GreenEnergy (AGE)'),
            new CardId('Boom - GreenEnergy (AGE)'),
        ];
    }
}
