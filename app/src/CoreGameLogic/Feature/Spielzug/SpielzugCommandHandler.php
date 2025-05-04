<?php

namespace Domain\CoreGameLogic\Feature\Spielzug;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Dto\Event\Player\SpielzugWasCompleted;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SpielzugAbschliessen;
use Domain\CoreGameLogic\GameState\CurrentPlayerAccessor;

final readonly class SpielzugCommandHandler implements CommandHandlerInterface
{
    public function canHandle(CommandInterface $command): bool
    {
        return $command instanceof SpielzugAbschliessen;
    }

    public function handle(CommandInterface $command, GameEvents $gameState): GameEvents
    {
        /** @phpstan-ignore-next-line */
        return match ($command::class) {
            SpielzugAbschliessen::class => $this->handleSpielzugAbschliessen($command, $gameState),
        };
    }

    private function handleSpielzugAbschliessen(SpielzugAbschliessen $command, GameEvents $gameState): GameEvents
    {
        $currentPlayer = CurrentPlayerAccessor::forStream($gameState);
        if (!$currentPlayer->equals($command->playerId)) {
            throw new \RuntimeException('Only the current player can complete a turn', 1649582779);
        }

        return GameEvents::with(
            new SpielzugWasCompleted($command->playerId)
        );
    }
}
