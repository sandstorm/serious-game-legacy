<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\CommandHandler;

use Domain\CoreGameLogic\EventStore\GameEvents;

/**
 * Common interface for all Game Command command handlers
 *
 * @internal no public API, because commands are no extension points
 */
interface CommandHandlerInterface
{
    public function canHandle(CommandInterface $command): bool;

    public function handle(CommandInterface $command, GameEvents $gameState): GameEvents;
}
