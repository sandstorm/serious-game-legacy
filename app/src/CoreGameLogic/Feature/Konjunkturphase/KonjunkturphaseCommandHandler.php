<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\CardOrder;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\CardsWereShuffled;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\ValueObject\CurrentYear;
use Domain\CoreGameLogic\Feature\Konjunkturphase\ValueObject\Zinssatz;
use Domain\Definitions\Card\PileFinder;
use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
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
                year: new CurrentYear($year),
                type: $nextKonjunkturphase->type,
                zinssatz: new Zinssatz($nextKonjunkturphase->zinssatz),
                kompetenzbereiche: $nextKonjunkturphase->kompetenzbereiche,
                zeitsteineForPlayers: KonjunkturphaseState::calculateInitialZeitsteineForPlayers($gameState),
            ),

            // We ALSO SHUFFLE cards during Konjunkturphasenwechsel
            ...$this->handleShuffleCards($command)->events
        );
    }

    private function handleShuffleCards(ChangeKonjunkturphase $command): GameEventsToPersist
    {
        if (isset($command->fixedCardOrderForTesting) && count($command->fixedCardOrderForTesting) > 0) {
            return GameEventsToPersist::with(
                new CardsWereShuffled($command->fixedCardOrderForTesting)
            );
        }

        $piles = [];
        foreach (PileId::cases() as $pileId) {
            $cards = $this->shuffleCards(PileFinder::getCardsIdsForPile($pileId));

            $piles[] = new CardOrder(
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
