<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Pile;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Dto\ValueObject\CardId;
use Domain\CoreGameLogic\Dto\ValueObject\PileId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Pile\Command\ShuffleCards;
use Domain\CoreGameLogic\Feature\Pile\Event\CardsWereShuffled;
use Domain\CoreGameLogic\Feature\Pile\State\dto\Pile;
use Domain\Definitions\Pile\Enum\PileEnum;
use Domain\Definitions\Pile\PileFinder;
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

    /**
     * @param CardId[] $cards
     * @return CardId[]
     */
    private function shuffleCards(array $cards): array
    {
        $randomizer = new Randomizer();
        return $randomizer->shuffleArray($cards);
    }

    private function handleShuffleCards(ShuffleCards $command): GameEventsToPersist
    {
        if (isset($command->fixedCardIdOrderingForTesting) && is_array($command->fixedCardIdOrderingForTesting)) {
            return GameEventsToPersist::with(
                new CardsWereShuffled($command->fixedCardIdOrderingForTesting)
            );
        }

        $piles = [];
        foreach (PileEnum::cases() as $case) {
            $piles[] = new Pile(
                pileId: new PileId($case),
                cards: $this->shuffleCards(PileFinder::getCardsIdsForPile(new PileId($case)))
            );
        }

        return GameEventsToPersist::with(
            new CardsWereShuffled($piles)
        );
    }
}
