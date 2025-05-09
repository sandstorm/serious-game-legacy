<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Jahreswechsel;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Jahreswechsel\Command\StartNewYear;
use Domain\CoreGameLogic\Feature\Jahreswechsel\Event\NewYearWasStarted;

/**
 * @internal no public API, because commands are no extension points. ALWAYS USE {@see ForCoreGameLogic::handle()} to trigger commands.
 */
final readonly class JahreswechselCommandHandler implements CommandHandlerInterface
{
    public function canHandle(CommandInterface $command): bool
    {
        return $command instanceof StartNewYear;
    }

    public function handle(CommandInterface $command, GameEvents $gameState): GameEventsToPersist
    {
        /** @phpstan-ignore-next-line */
        return match ($command::class) {
            StartNewYear::class => $this->handleStartNewYear($command, $gameState),
        };
    }

    private function handleStartNewYear(StartNewYear $command, GameEvents $gameState): GameEventsToPersist
    {
        return GameEventsToPersist::with(
            new NewYearWasStarted(
                newYear: $command->newYear,
                leitzins: $command->leitzins,
            )
        );
    }
}
