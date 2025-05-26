<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\DrivingPorts;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\GameId;

/**
 * Driving Port for core business operations
 *
 * This is the primary entry point into the domain from external code:
 * - External code (e.g. controllers) depend on this interface
 * - The implementation {@see CoreGameLogicApp} contains the actual logic
 * - Methods define the allowed operations on this domain
 *
 * Because this is wired in AppServiceProvider, you can *inject this everywhere*
 * in Laravel.
 *
 * @api Main entry point into the core domain
 */
interface ForCoreGameLogic
{
    public function hasGame(GameId $gameId): bool;

    public function getGameEvents(GameId $gameId): GameEvents;

    /**
     * Handle a command
     * @param GameId $gameId
     * @param CommandInterface $command
     * @return void
     */
    public function handle(GameId $gameId, CommandInterface $command): void;

}
