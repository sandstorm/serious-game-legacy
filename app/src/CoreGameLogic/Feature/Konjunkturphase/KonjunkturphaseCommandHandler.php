<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Dto\ValueObject\Kompetenzbereich;
use Domain\CoreGameLogic\Dto\ValueObject\Konjunkturphase;
use Domain\CoreGameLogic\Dto\ValueObject\Leitzins;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ChangeKonjunkturphase;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Command\ShuffleCards;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\CardOrder;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\CardsWereShuffled;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Konjunkturphase\ValueObject\CurrentYear;
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
        return $command instanceof ChangeKonjunkturphase
            || $command instanceof ShuffleCards;
    }

    public function handle(CommandInterface $command, GameEvents $gameState): GameEventsToPersist
    {
        /** @phpstan-ignore-next-line */
        return match ($command::class) {
            ChangeKonjunkturphase::class => $this->handleChangeKonjunkturphase($command, $gameState),
            ShuffleCards::class => $this->handleShuffleCards($command, $gameState),
        };
    }

    public function handleChangeKonjunkturphase(ChangeKonjunkturphase $command, GameEvents $gameState): GameEventsToPersist
    {
        if (!GamePhaseState::isInGamePhase($gameState)) {
            throw new \RuntimeException('not in game phase', 1747148685);
        }

        $year = 1;
        // increment year
        if (GamePhaseState::currentKonjunkturphaseOrNull($gameState)?->year->value > 0) {
            $year = GamePhaseState::currentKonjunkturphase($gameState)->year->value + 1;
        }

        $idsOfPastKonjunkturphasen = $this->getIdsOfPastKonjunkturphasen($gameState);

        // We pick a random next konjunkturphase from the definitions that was not used yet.
        // If the max amount of defined konjunkturphasen is reached, we restart the konjunkturphasen.
        $nextKonjunkturphase = $command->fixedKonjunkturphaseForTesting ?? KonjunkturphaseFinder::getUnusedRandomKonjunkturphase($idsOfPastKonjunkturphasen);

        return GameEventsToPersist::with(
            new KonjunkturphaseWasChanged(
                konjunkturphase: new Konjunkturphase(
                    id: $nextKonjunkturphase->id,
                    year: new CurrentYear($year),
                    type: $nextKonjunkturphase->type,
                    leitzins: new Leitzins($nextKonjunkturphase->leitzins),
                    kompetenzbereiche: array_map(
                        fn($kompetenzbereich) => new Kompetenzbereich(
                            name: $kompetenzbereich->name,
                            kompetenzsteine: $kompetenzbereich->kompetenzsteine,
                        ),
                        $nextKonjunkturphase->kompetenzbereiche
                    )
                ),
            ),

            // We ALSO SHUFFLE cards during Konjunkturphasenwechsel
            ...$this->handleShuffleCards($command)->events
        );
        return $eventsToPersist;
    }

    /**
     * Public for testing purposes only.
     * Returns the ids of the last konjunkturphasen limited to the maxium amount of defined konjunkturphasen.
     *
     * @param GameEvents $gameState
     * @return array<int>
     */
    public function getIdsOfPastKonjunkturphasen(GameEvents $gameState): array
    {
        // ids of all the past konjunkturphasen
        $idsOfPastKonjunkturphasen = GamePhaseState::idsOfPastKonjunkturphasen($gameState);
        $amountOfPastIds = count($idsOfPastKonjunkturphasen);
        // amount of konjunkturphasen defined in the definitions
        $amountOfKonjunkturphasen = count(KonjunkturphaseFinder::getAllKonjunkturphasen());

        // Returns empty list if the amount of konjunkturphasen is 0 or a multiple of the amount of konjunkturphasen
        // aka we have reached the max amount of konjunkturphasen and start over
        if ($amountOfPastIds % $amountOfKonjunkturphasen === 0) {
            return [];
        }

        // Returns the ids of the last konjunkturphasen, picking from the end of the array
        return array_slice(
            $idsOfPastKonjunkturphasen,
            $amountOfPastIds - $amountOfPastIds % $amountOfKonjunkturphasen,
            null,
            true
        );
    }


    private function handleShuffleCards(ShuffleCards|ChangeKonjunkturphase $command): GameEventsToPersist
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
