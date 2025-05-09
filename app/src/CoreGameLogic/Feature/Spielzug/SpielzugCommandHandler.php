<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SpielzugAbschliessen;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasSkipped;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasActivated;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasCompleted;
use Domain\CoreGameLogic\Feature\Spielzug\Event\TriggeredEreignis;
use Domain\CoreGameLogic\GameState\CurrentPlayerAccessor;

/**
 * @internal no public API, because commands are no extension points. ALWAYS USE {@see ForCoreGameLogic::handle()} to trigger commands.
 */
final readonly class SpielzugCommandHandler implements CommandHandlerInterface
{
    public function canHandle(CommandInterface $command): bool
    {
        return $command instanceof ActivateCard
            || $command instanceof SkipCard
            || $command instanceof SpielzugAbschliessen;
    }

    public function handle(CommandInterface $command, GameEvents $gameState): GameEventsToPersist
    {
        /** @phpstan-ignore-next-line */
        return match ($command::class) {
            ActivateCard::class => $this->handleActivateCard($command, $gameState),
            SkipCard::class => $this->handleSkipCard($command, $gameState),
            SpielzugAbschliessen::class => $this->handleSpielzugAbschliessen($command, $gameState),
        };
    }

    private function handleActivateCard(ActivateCard $command, GameEvents $gameState): GameEventsToPersist
    {
        $currentPlayer = CurrentPlayerAccessor::forStream($gameState);
        if (!$currentPlayer->equals($command->player)) {
            throw new \RuntimeException('Only the current player can complete a turn', 1649582779);
        }

        $events = GameEventsToPersist::with(
            new CardWasActivated($command->player, $command->card)
        );

        if ($command->attachedEreignis !== null) {
            $events = $events->withAppendedEvents(
                new TriggeredEreignis($command->player, $command->attachedEreignis)
            );
        }

        return $events;
    }

    private function handleSkipCard(SkipCard $command, GameEvents $gameState): GameEventsToPersist
    {
        $currentPlayer = CurrentPlayerAccessor::forStream($gameState);
        if (!$currentPlayer->equals($command->player)) {
            throw new \RuntimeException('Only the current player can complete a turn', 1649582779);
        }

        return GameEventsToPersist::with(
            new CardWasSkipped($command->player, $command->card)
        );
    }

    private function handleSpielzugAbschliessen(SpielzugAbschliessen $command, GameEvents $gameState): GameEventsToPersist
    {
        $currentPlayer = CurrentPlayerAccessor::forStream($gameState);
        if (!$currentPlayer->equals($command->player)) {
            throw new \RuntimeException('Only the current player can complete a turn', 1649582779);
        }

        return GameEventsToPersist::with(
            new SpielzugWasCompleted($command->player)
        );
    }
}
