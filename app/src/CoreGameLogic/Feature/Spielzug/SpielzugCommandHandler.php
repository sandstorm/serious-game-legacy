<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Dto\Event\Player\LebenszielChosen;
use Domain\CoreGameLogic\Dto\Event\Player\SpielzugWasCompleted;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Command\LebenszielAuswaehlen;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SpielzugAbschliessen;
use Domain\CoreGameLogic\GameState\CurrentPlayerAccessor;
use Domain\CoreGameLogic\GameState\LebenszielAccessor;

final readonly class SpielzugCommandHandler implements CommandHandlerInterface
{
    public function canHandle(CommandInterface $command): bool
    {
        return $command instanceof SpielzugAbschliessen
            || $command instanceof LebenszielAuswaehlen;
    }

    public function handle(CommandInterface $command, GameEvents $gameState): GameEvents
    {
        /** @phpstan-ignore-next-line */
        return match ($command::class) {
            SpielzugAbschliessen::class => $this->handleSpielzugAbschliessen($command, $gameState),
            LebenszielAuswaehlen::class => $this->handleLebenszielAuswaehlen($command, $gameState),
        };
    }

    private function handleSpielzugAbschliessen(SpielzugAbschliessen $command, GameEvents $gameState): GameEvents
    {
        $currentPlayer = CurrentPlayerAccessor::forStream($gameState);
        if (!$currentPlayer->equals($command->playerId)) {
            throw new \RuntimeException('Only the current player can complete a turn', 1649582779);
        }

        return $gameState->withAppendedEvents(GameEvents::fromArray([
            new SpielzugWasCompleted($command->playerId)
        ]));
    }

    private function handleLebenszielAuswaehlen(LebenszielAuswaehlen $command, GameEvents $gameState): GameEvents
    {
        $currentPlayer = CurrentPlayerAccessor::forStream($gameState);
        if (!$currentPlayer->equals($command->playerId)) {
            throw new \RuntimeException('Only the current player can complete a turn', 1746700791);
        }

        $hasLebensziel = LebenszielAccessor::forStream($gameState)->forPlayer($command->playerId);
        if ($hasLebensziel !== null) {
            throw new \RuntimeException('Player has already selected a Lebensziel', 1746713490);
        }

        return $gameState->withAppendedEvents(GameEvents::fromArray([
            new LebenszielChosen(
                player: $command->playerId,
                lebensziel: $command->lebensziel,
            )
        ]));
    }
}
