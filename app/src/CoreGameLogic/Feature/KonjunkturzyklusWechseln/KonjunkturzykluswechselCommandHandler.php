<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\KonjunkturzyklusWechseln;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Dto\ValueObject\CurrentYear;
use Domain\CoreGameLogic\Dto\ValueObject\Kompetenzbereich;
use Domain\CoreGameLogic\Dto\ValueObject\Konjunkturzyklus;
use Domain\CoreGameLogic\Dto\ValueObject\Leitzins;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\KonjunkturzyklusWechseln\Command\KonjunkturzyklusWechseln;
use Domain\CoreGameLogic\Feature\KonjunkturzyklusWechseln\Event\KonjunkturzyklusWechselExecuted;
use Domain\Definitions\Konjunkturzyklus\KonjunkturzyklusFinder;

/**
 * @internal no public API, because commands are no extension points. ALWAYS USE {@see ForCoreGameLogic::handle()} to trigger commands.
 */
final readonly class KonjunkturzykluswechselCommandHandler implements CommandHandlerInterface
{
    public function canHandle(CommandInterface $command): bool
    {
        return $command instanceof KonjunkturzyklusWechseln;
    }

    public function handle(CommandInterface $command, GameEvents $gameState): GameEventsToPersist
    {
        /** @phpstan-ignore-next-line */
        return match ($command::class) {
            KonjunkturzyklusWechseln::class => $this->handleKonjunkturzykluswechsel($command, $gameState),
        };
    }

    public function handleKonjunkturzykluswechsel(KonjunkturzyklusWechseln $command, GameEvents $gameState): GameEventsToPersist
    {
        if (!GamePhaseState::isInGamePhase($gameState)) {
            throw new \RuntimeException('not in game phase', 1747148685);
        }

        $year = 1;
        // increment year
        if (GamePhaseState::currentKonjunkturzyklus($gameState)?->year->value > 0) {
            $year = GamePhaseState::currentKonjunkturzyklus($gameState)->year->value + 1;
        }

        $idsOfPastKonjunkturzyklen = $this->getIdsOfPastKonjunkturzyklen($gameState);

        // We pick a random next konjunkturzyklus from the definitions that was not used yet.
        // If the max amount of defined konjunkturzyklen is reached, we restart the konjunkturzyklen.
        $nextKonjunkturZyklus = $command->fixedKonjunkturzyklusForTesting ?? KonjunkturzyklusFinder::getUnusedRandomKonjunkturzyklus($idsOfPastKonjunkturzyklen);

        return GameEventsToPersist::with(
            new KonjunkturzyklusWechselExecuted(
                year: new CurrentYear($year),
                konjunkturzyklus: new Konjunkturzyklus(
                    id: $nextKonjunkturZyklus->id,
                    type: $nextKonjunkturZyklus->type,
                    leitzins: new Leitzins($nextKonjunkturZyklus->leitzins),
                    kompetenzbereiche: array_map(
                        fn($kompetenzbereich) => new Kompetenzbereich(
                            name: $kompetenzbereich->name,
                            kompetenzsteine: $kompetenzbereich->kompetenzsteine,
                        ),
                        $nextKonjunkturZyklus->kompetenzbereiche
                    )
                ),
            )
        );
    }

    /**
     * Public for testing purposes only.
     * Returns the ids of the last konjunkturzyklen limited to the maxium amount of defined konjunkturzyklen.
     *
     * @param GameEvents $gameState
     * @return array<int>
     */
    public function getIdsOfPastKonjunkturzyklen(GameEvents $gameState): array
    {
        // ids of all the past konjunkturzyklen
        $idsOfPastKonjunkturzyklen = GamePhaseState::idsOfPastKonjunkturzyklen($gameState);
        $amountOfPastIds = count($idsOfPastKonjunkturzyklen);
        // amount of konjunkturzyklen defined in the definitions
        $amountOfKonjunkturzyklen = count(KonjunkturzyklusFinder::getAllKonjunkturzyklen());

        // Returns empty list if the amount of konjunkturzyklen is 0 or a multiple of the amount of konjunkturzyklen
        // aka we have reached the max amount of konjunkturzyklen and start over
        if ($amountOfPastIds % $amountOfKonjunkturzyklen === 0) {
            return [];
        }

        // Returns the ids of the last konjunkturzyklen, picking from the end of the array
        return array_slice(
            $idsOfPastKonjunkturzyklen,
            $amountOfPastIds - $amountOfPastIds % $amountOfKonjunkturzyklen,
            null,
            true
        );
    }
}
