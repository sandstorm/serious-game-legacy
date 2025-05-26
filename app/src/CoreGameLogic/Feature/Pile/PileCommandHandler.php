<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Pile;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ShuffleCards;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\CardsWereShuffled;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\Pile;
use Domain\Definitions\Card\PileFinder;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;
use Random\Randomizer;

/**
 * @internal no public API, because commands are no extension points. ALWAYS USE {@see ForCoreGameLogic::handle()} to trigger commands.
 */
final readonly class PileCommandHandler implements CommandHandlerInterface
{
    public function canHandle(CommandInterface $command): bool
    {
        return $command instanceof ShuffleCards;
    }

    public function handle(CommandInterface $command, GameEvents $gameState): GameEventsToPersist
    {
        /** @phpstan-ignore-next-line */
        return match ($command::class) {
            ShuffleCards::class => $this->handleShuffleCards($command),
        };
    }

    private function handleShuffleCards(ShuffleCards $command): GameEventsToPersist
    {
        if (isset($command->fixedCardIdOrderingForTesting) && count($command->fixedCardIdOrderingForTesting) > 0) {
            return GameEventsToPersist::with(
                new CardsWereShuffled($command->fixedCardIdOrderingForTesting)
            );
        }

        $piles = [];
        foreach (PileId::cases() as $pileId) {
            $cards = $this->shuffleCards(PileFinder::getCardsIdsForPile($pileId));

            $piles[] = new Pile(
                pileId: $pileId,
                cards: $cards
            );
        }

        return GameEventsToPersist::with(
            new CardsWereShuffled($piles)
        );
    }

    /**
     * @param CardId[] $cards
     * @return CardId[]
     */
    private function shuffleCards(array $cards): array
    {
        $randomizer = new Randomizer();
        return $randomizer->shuffleArray($cards);
    }
}
