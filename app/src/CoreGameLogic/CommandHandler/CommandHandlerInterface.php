<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\CommandHandler;

use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;

/**
 * Common interface for all Game Command command handlers
 *
 * @internal no public API, because commands are no extension points. ALWAYS USE {@see ForCoreGameLogic::handle()} to trigger commands.
 */
interface CommandHandlerInterface
{
    public function canHandle(CommandInterface $command): bool;

    public function handle(CommandInterface $command, GameEvents $gameState): GameEventsToPersist;
}
