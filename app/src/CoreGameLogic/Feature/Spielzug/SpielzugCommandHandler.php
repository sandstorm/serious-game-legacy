<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\AcceptJobOffersAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\ActivateCardAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\SkipCardAktion as SkipCardAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Command\AcceptJobOffer;
use Domain\CoreGameLogic\Feature\Spielzug\Command\ActivateCard;
use Domain\CoreGameLogic\Feature\Spielzug\Command\EndSpielzug;
use Domain\CoreGameLogic\Feature\Spielzug\Command\RequestJobOffers;
use Domain\CoreGameLogic\Feature\Spielzug\Command\SkipCard;
use Domain\CoreGameLogic\Feature\Spielzug\Event\EreignisWasTriggered;

/**
 * @internal no public API, because commands are no extension points. ALWAYS USE {@see ForCoreGameLogic::handle()} to trigger commands.
 */
final readonly class SpielzugCommandHandler implements CommandHandlerInterface
{
    public function canHandle(CommandInterface $command): bool
    {
        return $command instanceof SkipCard
            || $command instanceof ActivateCard
            || $command instanceof RequestJobOffers
            || $command instanceof AcceptJobOffer
            || $command instanceof EndSpielzug;
    }

    public function handle(CommandInterface $command, GameEvents $gameState): GameEventsToPersist
    {
        /** @phpstan-ignore-next-line */
        return match ($command::class) {
            SkipCard::class => $this->handleSkipCard($command, $gameState),
            ActivateCard::class => $this->handleActivateCard($command, $gameState),
            RequestJobOffers::class => $this->handleRequestJobOffers($command, $gameState),
            AcceptJobOffer::class => $this->handleAcceptJobOffer($command, $gameState),
            EndSpielzug::class => $this->handleEndSpielzug($command, $gameState),
        };
    }

    private function handleEndSpielzug(EndSpielzug $command, GameEvents $gameEvents): GameEventsToPersist
    {
        $endSpielzugAktion = new Aktion\EndSpielzugAktion();
        return $endSpielzugAktion->execute($command->player, $gameEvents);
    }

    private function handleRequestJobOffers(RequestJobOffers $command, GameEvents $gameEvents): GameEventsToPersist
    {
        $aktion = new Aktion\RequestJobOffersAktion();
        return $aktion->execute($command->player, $gameEvents);
    }

    private function handleAcceptJobOffer(AcceptJobOffer $command, GameEvents $gameEvents): GameEventsToPersist
    {
        $aktion = new AcceptJobOffersAktion($command->jobId);
        return $aktion->execute($command->player, $gameEvents);
    }

    private function handleActivateCard(
        ActivateCard $command,
        GameEvents $gameEvents
    ): GameEventsToPersist {
        $aktion = new ActivateCardAktion($command->category);
        $events = $aktion->execute($command->player, $gameEvents);
        if ($command->attachedEreignis !== null) {
            $events = $events->withAppendedEvents(
                new EreignisWasTriggered($command->player, $command->attachedEreignis)
            );
        }
        return $events;
    }

    private function handleSkipCard(SkipCard $command, GameEvents $gameState): GameEventsToPersist
    {
        $aktion = new SkipCardAktion($command->category);
        return $aktion->execute($command->player, $gameState);
    }

}
