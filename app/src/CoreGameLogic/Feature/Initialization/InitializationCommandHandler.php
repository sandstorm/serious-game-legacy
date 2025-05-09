<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Dto\ValueObject\CurrentYear;
use Domain\CoreGameLogic\Dto\ValueObject\Leitzins;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Initialization\Command\DefinePlayerOrdering;
use Domain\CoreGameLogic\Feature\Initialization\Command\LebenszielAuswaehlen;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartGame;
use Domain\CoreGameLogic\Feature\Initialization\Event\LebenszielChosen;
use Domain\CoreGameLogic\Feature\Initialization\Event\PlayerOrderingWasDefined;
use Domain\CoreGameLogic\Feature\Initialization\State\LebenszielAccessor;
use Domain\CoreGameLogic\Feature\Jahreswechsel\Event\NewYearWasStarted;

/**
 * @internal no public API, because commands are no extension points. ALWAYS USE {@see ForCoreGameLogic::handle()} to trigger commands.
 */
final readonly class InitializationCommandHandler implements CommandHandlerInterface
{
    public function canHandle(CommandInterface $command): bool
    {
        return $command instanceof DefinePlayerOrdering
            || $command instanceof LebenszielAuswaehlen
            || $command instanceof StartGame;
    }

    public function handle(CommandInterface $command, GameEvents $gameState): GameEventsToPersist
    {
        /** @phpstan-ignore-next-line */
        return match ($command::class) {
            DefinePlayerOrdering::class => $this->handleDefinePlayerOrdering($command, $gameState),
            LebenszielAuswaehlen::class => $this->handleLebenszielAuswaehlen($command, $gameState),
            StartGame::class => $this->handleStartGame($command, $gameState),
        };
    }

    private function handleDefinePlayerOrdering(DefinePlayerOrdering $command, GameEvents $gameState): GameEventsToPersist
    {
        return GameEventsToPersist::with(
            new PlayerOrderingWasDefined(playerOrdering: $command->playerOrdering),
        );
    }

    private function handleLebenszielAuswaehlen(LebenszielAuswaehlen $command, GameEvents $gameState): GameEventsToPersist
    {
        $hasLebensziel = LebenszielAccessor::forStream($gameState)->forPlayer($command->playerId);
        if ($hasLebensziel !== null) {
            throw new \RuntimeException('Player has already selected a Lebensziel', 1746713490);
        }

        return GameEventsToPersist::with(
            new LebenszielChosen(
                player: $command->playerId,
                lebensziel: $command->lebensziel,
            )
        );
    }

    private function handleStartGame(StartGame $command, GameEvents $gameState): GameEventsToPersist
    {
        if (count($gameState) > 0) {
            throw new \RuntimeException('Game has already started', 1746713490);
        }

        return GameEventsToPersist::with(
            new PlayerOrderingWasDefined(
                playerOrdering: $command->playerOrdering
            ),
            // TODO: this cannot be hardcoded here :) Maybe delegate to the other command handler??
            new NewYearWasStarted(
                newYear: new CurrentYear(1),
                leitzins: new Leitzins(3)
            ),
        );
    }
}
