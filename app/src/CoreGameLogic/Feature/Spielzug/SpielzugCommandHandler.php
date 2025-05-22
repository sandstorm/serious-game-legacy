<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Dto\ValueObject\ResourceChanges;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Pile\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SpielzugAbschliessen;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasActivated;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasSkipped;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasCompleted;
use Domain\CoreGameLogic\Feature\Spielzug\Event\TriggeredEreignis;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\Definitions\Cards\CardFinder;
use Domain\Definitions\Cards\Model\CardDefinition;

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
        // TODO use up one Zeitstein when activating a card?

        $currentPlayer = CurrentPlayerAccessor::forStream($gameState);
        if (!$currentPlayer->equals($command->player)) {
            throw new \RuntimeException('Only the current player can complete a turn', 1649582779);
        }

        $topCardOnPile = PileState::topCardIdForPile($gameState, $command->pile);
        if (!$topCardOnPile->equals($command->cardId)) {
            throw new \RuntimeException('Only the top card of the pile can be activated', 1747326086);
        }

        $card = $command->fixedCardDefinitionForTesting !== null
            ? $command->fixedCardDefinitionForTesting
            : CardFinder::getCardById($command->cardId);

        $events = GameEventsToPersist::with(
            new CardWasActivated($command->player, $command->pile, $card->id, $card->resourceChanges)
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
            throw new \RuntimeException('Only the current player can skip a card', 1649582789);
        }

        $topCardOnPile = PileState::topCardIdForPile($gameState, $command->pile);
        if (!$topCardOnPile->equals($command->card)) {
            throw new \RuntimeException('Only the top card of the pile can be skipped', 1747325793);
        }

        return GameEventsToPersist::with(
            new CardWasSkipped($command->player, $command->card, $command->pile)
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
