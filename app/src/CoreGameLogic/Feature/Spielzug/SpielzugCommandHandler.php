<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Pile\State\PileState;
use Domain\CoreGameLogic\Feature\Player\State\PlayerState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasActivated;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasSkipped;
use Domain\CoreGameLogic\Feature\Spielzug\Event\EreignisWasTriggered;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasEnded;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\ResourceChanges;

/**
 * @internal no public API, because commands are no extension points. ALWAYS USE {@see ForCoreGameLogic::handle()} to trigger commands.
 */
final readonly class SpielzugCommandHandler implements CommandHandlerInterface
{
    public function canHandle(CommandInterface $command): bool
    {
        return $command instanceof ActivateCard
            || $command instanceof SkipCard
            || $command instanceof EndSpielzug;
    }

    public function handle(CommandInterface $command, GameEvents $gameState): GameEventsToPersist
    {
        /** @phpstan-ignore-next-line */
        return match ($command::class) {
            ActivateCard::class => $this->handleActivateCard($command, $gameState),
            SkipCard::class => $this->handleSkipCard($command, $gameState),
            EndSpielzug::class => $this->handleSpielzugAbschliessen($command, $gameState),
        };
    }

    private function handleActivateCard(ActivateCard $command, GameEvents $gameState): GameEventsToPersist
    {
        $currentPlayer = CurrentPlayerAccessor::forStream($gameState);
        if (!$currentPlayer->equals($command->player)) {
            throw new \RuntimeException('Only the current player can activate a card', 1747917492);
        }

        $topCardOnPile = PileState::topCardIdForPile($gameState, $command->pile);

        if (!$topCardOnPile->equals($command->cardId)) {
            throw new \RuntimeException('Only the top card of the pile can be activated', 1747326086);
        }

        $card = $command->fixedCardDefinitionForTesting !== null ? $command->fixedCardDefinitionForTesting : CardFinder::getCardById($command->cardId);

        $costToActivate = new ResourceChanges(
            zeitsteineChange: AktionsCalculator::forStream($gameState)->hasPlayerSkippedACardThisRound($command->player) ? 0 : -1
        );
        $totalCosts = $costToActivate->accumulate($card->resourceChanges);
        if (!AktionsCalculator::forStream($gameState)->canPlayerAffordAction($command->player, $totalCosts)) {
            throw new \RuntimeException('Player ' . $command->player->value . ' does not have the required resources ('. PlayerState::getResourcesForPlayer($gameState, $command->player).' to activate the card ' . $card->id->value . ' (' . $totalCosts .')', 1747920761);
        }

        $events = GameEventsToPersist::with(
            new CardWasActivated($command->player, $command->pile, $card->id, $totalCosts)
        );

        if ($command->attachedEreignis !== null) {
            $events = $events->withAppendedEvents(
                new EreignisWasTriggered($command->player, $command->attachedEreignis)
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

        if (!AktionsCalculator::forStream($gameState)->canPlayerAffordAction($command->player, new ResourceChanges(zeitsteineChange: 1))) {
            throw new \RuntimeException('Player ' . $command->player->value . ' does not have the required resources to skip the card ' . $command->card->value, 1747991385);
        }

        return GameEventsToPersist::with(
            new CardWasSkipped($command->player, $command->card, $command->pile)
        );
    }

    private function handleSpielzugAbschliessen(EndSpielzug $command, GameEvents $gameState): GameEventsToPersist
    {
        $currentPlayer = CurrentPlayerAccessor::forStream($gameState);
        if (!$currentPlayer->equals($command->player)) {
            throw new \RuntimeException('Only the current player can complete a turn', 1649582779);
        }

        return GameEventsToPersist::with(
            new SpielzugWasEnded($command->player)
        );
    }
}
