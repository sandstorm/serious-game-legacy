<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Initialization\Command\DefinePlayerOrdering;
use Domain\CoreGameLogic\Feature\Initialization\Command\LebenszielAuswaehlen;
use Domain\CoreGameLogic\Feature\Initialization\Event\LebenszielChosen;
use Domain\CoreGameLogic\Feature\Initialization\Event\PlayerOrderingWasDefined;
use Domain\CoreGameLogic\GameState\CurrentPlayerAccessor;
use Domain\CoreGameLogic\GameState\LebenszielAccessor;

/**
 * @internal no public API, because commands are no extension points. ALWAYS USE {@see ForCoreGameLogic::handle()} to trigger commands.
 */
final readonly class InitializationCommandHandler implements CommandHandlerInterface
{
    public function canHandle(CommandInterface $command): bool
    {
        return $command instanceof DefinePlayerOrdering
            || $command instanceof LebenszielAuswaehlen;
    }

    public function handle(CommandInterface $command, GameEvents $gameState): GameEventsToPersist
    {
        /** @phpstan-ignore-next-line */
        return match ($command::class) {
            DefinePlayerOrdering::class => $this->handleDefinePlayerOrdering($command, $gameState),
            LebenszielAuswaehlen::class => $this->handleLebenszielAuswaehlen($command, $gameState),
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
        $currentPlayer = CurrentPlayerAccessor::forStream($gameState);
        if (!$currentPlayer->equals($command->playerId)) {
            throw new \RuntimeException('Only the current player can complete a turn', 1746700791);
        }

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
}
