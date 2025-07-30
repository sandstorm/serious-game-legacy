<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\CardsWereShuffled;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\StockPriceState;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\Pile;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;
use Random\RandomException;
use Random\Randomizer;

/**
 * @internal no public API, because commands are no extension points. ALWAYS USE {@see ForCoreGameLogic::handle()} to trigger commands.
 */
final readonly class KonjunkturphaseCommandHandler implements CommandHandlerInterface
{
    public function canHandle(CommandInterface $command): bool
    {
        return $command instanceof ChangeKonjunkturphase;
    }

    public function handle(CommandInterface $command, GameEvents $gameEvents): GameEventsToPersist
    {
        /** @phpstan-ignore-next-line */
        return match ($command::class) {
            ChangeKonjunkturphase::class => $this->handleChangeKonjunkturphase($command, $gameEvents),
        };
    }

    /**
     * Ends the current konjunkturphase and starts a new one.
     *
     * @param ChangeKonjunkturphase $command
     * @param GameEvents $gameState
     * @return GameEventsToPersist
     * @throws RandomException
     */
    public function handleChangeKonjunkturphase(
        ChangeKonjunkturphase $command,
        GameEvents $gameState
    ): GameEventsToPersist {
        if (!GamePhaseState::isInGamePhase($gameState)) {
            throw new \RuntimeException('not in game phase', 1747148685);
        }

        $year = 1;
        // increment year
        if (GamePhaseState::hasKonjunkturphase($gameState) && GamePhaseState::currentKonjunkturphasenYear($gameState)->value > 0) {
            $year = GamePhaseState::currentKonjunkturphasenYear($gameState)->value + 1;
        }

        $lastKonjunkturphaseType = $gameState->findLastOrNull(KonjunkturphaseWasChanged::class)->type ?? null;

        // We pick a random next konjunkturphase from the definitions.
        $nextKonjunkturphase = $command->fixedKonjunkturphaseForTesting ?? KonjunkturphaseFinder::getRandomKonjunkturphase($lastKonjunkturphaseType);

        return GameEventsToPersist::with(
            new KonjunkturphaseWasChanged(
                id: $nextKonjunkturphase->id,
                year: new Year($year),
                type: $nextKonjunkturphase->type,
                stockPrices: StockPriceState::calculateStockPrices($gameState),
            ),

            // We ALSO SHUFFLE cards during Konjunkturphasenwechsel
            ...$this->handleShuffleCards($command)->events
        );
    }

    private function handleShuffleCards(ChangeKonjunkturphase $command): GameEventsToPersist
    {
        if ($command->hasFixedCardOrderForTesting) {
            return GameEventsToPersist::with(
                new CardsWereShuffled(CardFinder::getInstance()->generatePilesFromCards())
            );
        }

        $piles = [];
        foreach (CardFinder::getInstance()->generatePilesFromCards() as $pile) {
            $cardIds = $this->shuffleCards($pile->getCardIds());

            $piles[] = new Pile(
                pileId: $pile->getPileId(),
                cardIds: $cardIds
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
