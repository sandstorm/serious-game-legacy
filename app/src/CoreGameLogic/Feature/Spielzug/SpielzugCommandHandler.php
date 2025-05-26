<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\PileState;
use Domain\CoreGameLogic\Feature\Spielzug\Command\AcceptJobOffer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\RequestJobOffers;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasActivated;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasSkipped;
use Domain\CoreGameLogic\Feature\Spielzug\Event\EreignisWasTriggered;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobOffersWereRequested;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobOfferWasAccepted;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasEnded;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\KategorieCardDefinition;
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
            || $command instanceof RequestJobOffers
            || $command instanceof AcceptJobOffer
            || $command instanceof EndSpielzug;
    }

    public function handle(CommandInterface $command, GameEvents $gameState): GameEventsToPersist
    {
        /** @phpstan-ignore-next-line */
        return match ($command::class) {
            ActivateCard::class => $this->handleActivateCard($command, $gameState),
            SkipCard::class => $this->handleSkipCard($command, $gameState),
            RequestJobOffers::class => $this->handleRequestJobOffers($command, $gameState),
            AcceptJobOffer::class => $this->handleAcceptJobOffer($command, $gameState),
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

        $card = $command->fixedCardDefinitionForTesting !== null ? $command->fixedCardDefinitionForTesting : CardFinder::getInstance()->getCardById($command->cardId);

        $costToActivate = new ResourceChanges(
            zeitsteineChange: AktionsCalculator::forStream($gameState)->hasPlayerSkippedACardThisRound($command->player) ? 0 : -1
        );

        $totalCosts = $card instanceof KategorieCardDefinition ? $costToActivate->accumulate($card->resourceChanges) : $costToActivate;
        if (!AktionsCalculator::forStream($gameState)->canPlayerAffordAction($command->player, $totalCosts)) {
            throw new \RuntimeException('Player ' . $command->player->value . ' does not have the required resources ('. PlayerState::getResourcesForPlayer($gameState, $command->player).' to activate the card ' . $card->getId()->value . ' (' . $totalCosts .')', 1747920761);
        }

        $events = GameEventsToPersist::with(
            new CardWasActivated($command->player, $command->pile, $card->getId(), $totalCosts)
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

    private function handleRequestJobOffers(RequestJobOffers $command, GameEvents $gameState): GameEventsToPersist
    {
        $currentPlayer = CurrentPlayerAccessor::forStream($gameState);
        if (!$currentPlayer->equals($command->player)) {
            throw new \RuntimeException('Only the current player can request job offers', 1748265940);
        }

        $jobs = CardFinder::getInstance()->getJobsBasedOnPlayerResources(PlayerState::getResourcesForPlayer($gameState, $command->player));

        return GameEventsToPersist::with(
            new JobOffersWereRequested($command->player, array_map(fn ($job) => $job->getId(), $jobs))
        );
    }


    private function getRequestedJobOffersForThisTurn(GameEvents $gameEvents): ?JobOffersWereRequested
    {
        $eventsThisTurn = AktionsCalculator::forStream($gameEvents)->getEventsThisTurn();
        return $eventsThisTurn->findLastOrNull(JobOffersWereRequested::class);
    }

    private function handleAcceptJobOffer(AcceptJobOffer $command, GameEvents $gameEvents): GameEventsToPersist
    {
        $currentPlayer = CurrentPlayerAccessor::forStream($gameEvents);
        if (!$currentPlayer->equals($command->player)) {
            throw new \RuntimeException('Only the current player can accept job offers', 1748349790);
        }
        $currentJobOffers = $this->getRequestedJobOffersForThisTurn($gameEvents);
        if ($currentJobOffers === null || !in_array($command->jobId->value, array_map(fn ($jobId) => $jobId->value, $currentJobOffers->jobs), true)) {
            throw new \RuntimeException('You can only accept jobs that have been offered to you', 1748350449);
        }


        /** @var JobCardDefinition $job */
        $job = CardFinder::getInstance()->getCardById($command->jobId);

        return GameEventsToPersist::with(
            new JobOfferWasAccepted($command->player, $command->jobId, $job->gehalt)
        );

    }
}
