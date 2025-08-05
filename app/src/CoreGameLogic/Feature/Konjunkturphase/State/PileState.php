<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\CardsWereShuffled;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\DrawsCard;
use Domain\Definitions\Card\Dto\Pile;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

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
     * @param GameEvents $gameEvents
     * @param PileId $pileId
     * @return CardId
     * @throws \RuntimeException
     */
    public static function topCardIdForPile(GameEvents $gameEvents, PileId $pileId): CardId
    {
        $currentPiles = $gameEvents->findLast(CardsWereShuffled::class)->piles;
        foreach ($currentPiles as $pile) {
            if ($pile->getPileId()->equals($pileId)) {
                $cardIndex = self::numberOfCardDrawsSinceLastShuffle($gameEvents, $pileId);
                if ($cardIndex >= count($pile->getCardIds())) {
                    throw new \RuntimeException("Card index ($cardIndex) out of bounds for pile ($pileId)", 1748003108);
                }

                return array_values($pile->getCardIds())[$cardIndex];
            }
        }

        throw new \RuntimeException("Pile ($pileId) not found");
    }

    /**
     * @param GameEvents $gameEvents
     * @param PileId $pileId
     * @return CardId[]
     */
    public static function getFirstThreeJobCardIds(GameEvents $gameEvents, PileId $pileId): array
    {
        /** @var Pile[] $cardPiles */
        $cardPiles = $gameEvents->findLast(CardsWereShuffled::class)->piles;
        // TODO use array_find after switching to PHP 8.4
        /** @var Pile $jobCardPile */
        $jobCardPile = array_values(array_filter($cardPiles, fn($pile) => $pile->getPileId()->equals($pileId)))[0];
        // each time a job offer was accepted we discard the two other job offers as well -> after accepting a
        // job three new cards get drawn from the job offer card pile
        $startIndex = self::numberOfCardDrawsSinceLastShuffle($gameEvents, $pileId) * 3;
        return array_slice($jobCardPile->getCardIds(), $startIndex, 3);
    }
}
