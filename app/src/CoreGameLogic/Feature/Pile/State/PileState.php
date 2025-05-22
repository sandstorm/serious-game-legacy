<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Pile\State;

use Domain\CoreGameLogic\Dto\ValueObject\CardId;
use Domain\CoreGameLogic\Dto\ValueObject\PileId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Pile\Event\Behavior\DrawsCard;
use Domain\CoreGameLogic\Feature\Pile\Event\CardsWereShuffled;

class PileState
{
    /**
     * Counts and returns the DrawsCard events for a pile since the last shuffle.
     * @param GameEvents $stream
     * @param PileId $pileId
     * @return int
     */
    private static function numberOfCardDrawsSinceLastShuffle(GameEvents $stream, PileId $pileId): int
    {
        $currentDrawEventsForPile = $stream->findAllAfterLastOfType(CardsWereShuffled::class)
            ->findAllOfType(DrawsCard::class)
            ->filter(fn (DrawsCard $event) => $event->getPileId()->equals($pileId));

        return count($currentDrawEventsForPile);
    }

    /**
     * Returns the CardId of the Card that is currently on top of a given pile.
     *
     * @param GameEvents $stream
     * @param PileId $pileId
     * @return CardId
     */
    public static function topCardIdForPile(GameEvents $stream, PileId $pileId): CardId
    {
        $currentPiles = $stream->findLast(CardsWereShuffled::class)->piles;
        foreach ($currentPiles as $pile) {
            if ($pile->pileId->equals($pileId)) {
                return $pile->cards[self::numberOfCardDrawsSinceLastShuffle($stream, $pileId)];
            }
        }

        throw new \RuntimeException("Pile ($pileId) not found");
    }
}
