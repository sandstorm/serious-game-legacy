<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\CardsWereShuffled;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\DrawsCard;
use Domain\Definitions\Card\Dto\Pile;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;

class PileState
{
    /**
     * Counts and returns the DrawsCard events for a pile since the last shuffle.
     *
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
     * Returns the first $amount card from the pile. Default is 3.
     *
     * Make sure that the amount **does not change** between calls to the same pile
     * or you may get the wrong cards.
     *
     * @param GameEvents $gameEvents
     * @param PileId $pileId
     * @param int $amount
     * @return CardId[]
     */
    public static function getFirstXCardsFromPile(GameEvents $gameEvents, PileId $pileId, int $amount = 3): array
    {
        /** @var Pile[] $cardPiles */
        $cardPiles = $gameEvents->findLast(CardsWereShuffled::class)->piles;
        /** @var Pile $pile */
        $pile = array_find($cardPiles, fn($pile) => $pile->getPileId()->equals($pileId));
        // each time a job offer was accepted we discard the two other job offers as well -> after accepting a
        // job three new cards get drawn from the job offer card pile
        $startIndex = self::numberOfCardDrawsSinceLastShuffle($gameEvents, $pileId) * $amount;
        return array_slice($pile->getCardIds(), $startIndex, $amount);
    }
}
