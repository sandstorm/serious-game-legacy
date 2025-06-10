<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\CardsWereShuffled;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\DrawsCard;
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
            ->filter(fn (DrawsCard $event) => $event->getPileId() === $pileId);

        return count($currentDrawEventsForPile);
    }

    /**
     * Returns the CardId of the Card that is currently on top of a given pile.
     *
     * @param GameEvents $stream
     * @param PileId $pileId
     * @return CardId
     * @throws \RuntimeException
     */
    public static function topCardIdForPile(GameEvents $stream, PileId $pileId): CardId
    {
        $currentPiles = $stream->findLast(CardsWereShuffled::class)->piles;
        foreach ($currentPiles as $pile) {
            if ($pile->pileId === $pileId) {
                $cardIndex = self::numberOfCardDrawsSinceLastShuffle($stream, $pileId);
                if ($cardIndex >= count($pile->cards)) {
                    throw new \RuntimeException("Card index ($cardIndex) out of bounds for pile ($pileId->value)", 1748003108);
                }

                return array_values($pile->cards)[$cardIndex];
            }
        }

        throw new \RuntimeException("Pile ($pileId->value) not found");
    }


    /**
     * @param CategoryId $category
     * @param int $phase
     * @return PileId
     *
     * TODO make Phase Value Object
     */
    public static function getPileIdForCategoryAndPhase(CategoryId $category, int $phase = 1): PileId
    {
        return match ($category) {
            CategoryId::BILDUNG_UND_KARRIERE => PileId::BILDUNG_PHASE_1,
            CategoryId::SOZIALES_UND_FREIZEIT => PileId::FREIZEIT_PHASE_1,
            CategoryId::JOBS => PileId::JOBS_PHASE_1,
            default => PileId::BILDUNG_PHASE_1, // TODO add all Categories and consider the phase
        };
    }
}
