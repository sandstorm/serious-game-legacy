<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\KonjunkturzyklusWechseln;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Dto\ValueObject\CurrentYear;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\KonjunkturzyklusWechseln\Command\KonjunkturzyklusWechseln;
use Domain\CoreGameLogic\Feature\KonjunkturzyklusWechseln\Event\KonjunkturzyklusWechselExecuted;

/**
 * @internal no public API, because commands are no extension points. ALWAYS USE {@see ForCoreGameLogic::handle()} to trigger commands.
 */
final readonly class JahreswechselCommandHandler implements CommandHandlerInterface
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
            throw new \RuntimeException('not in game phase', 1746713490);
        }

        $year = 1;
        // increment year
        if (GamePhaseState::currentYear($gameState)?->year->value > 0) {
            $year = GamePhaseState::currentYear($gameState)->year->value + 1;
        }

        // TODO write an repository of some sort that returns the current year randomly

        return GameEventsToPersist::with(
            new KonjunkturzyklusWechselExecuted(
                year: new CurrentYear($year),
                konjunkturzyklus: $command->konjunkturzyklus,
            ),
        );
    }
}
