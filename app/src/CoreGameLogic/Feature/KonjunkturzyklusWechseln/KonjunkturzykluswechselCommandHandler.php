<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\KonjunkturzyklusWechseln;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Dto\Enum\KompetenzbereichEnum;
use Domain\CoreGameLogic\Dto\ValueObject\CurrentYear;
use Domain\CoreGameLogic\Dto\ValueObject\Kompetenzbereich;
use Domain\CoreGameLogic\Dto\ValueObject\Konjunkturzyklus;
use Domain\CoreGameLogic\Dto\ValueObject\Leitzins;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\KonjunkturzyklusWechseln\Command\KonjunkturzyklusWechseln;
use Domain\CoreGameLogic\Feature\KonjunkturzyklusWechseln\Event\KonjunkturzyklusWechselExecuted;
use Domain\Definitions\KonjunkturzyklusDefinition\Repository\KonjunkturzyklusRepository;

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
            KonjunkturzyklusWechseln::class => $this->handleKonjunkturzykluswechsel($gameState),
        };
    }

    // TODO write handler for test with test command to return specific konjunkturzyklus
    public function handleKonjunkturzykluswechsel(GameEvents $gameState): GameEventsToPersist
    {
        if (!GamePhaseState::isInGamePhase($gameState)) {
            throw new \RuntimeException('not in game phase', 1747148685);
        }

        $year = 1;
        // increment year
        if (GamePhaseState::currentKonjunkturzyklus($gameState)?->year->value > 0) {
            $year = GamePhaseState::currentKonjunkturzyklus($gameState)->year->value + 1;
        }

        $idsOfPastKonjunkturzyklen = GamePhaseState::idsOfPastKonjunkturzyklen($gameState);

        $nextKonjunkturZyklus = KonjunkturzyklusRepository::getUnusedRandomKonjunkturzyklus($idsOfPastKonjunkturzyklen);

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
}
