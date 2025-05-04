<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\DrivingPorts;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\EventStore\GameEvents;

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
 * Example usage from a Laravel controller:
 *
 * ```php
 * class TimeController {
 *     public function __construct(
 *         private readonly ForDoingCoreBusinessLogic $core
 *     ) {}
 * }
 * ```
 *
 * TODO: RENAME to the core of the business logic you want to model.
 *
 * @api Main entry point into the core domain
 */
interface ForCoreGameLogic
{
    public function startGameIfNotStarted(GameId $gameId): void;

    public function getGameStream(GameId $gameId): GameEvents;

    /**
     * Handle a command
     * @param GameId $gameId
     * @param CommandInterface $command
     * @return void
     */
    public function handle(GameId $gameId, CommandInterface $command): void;
}
