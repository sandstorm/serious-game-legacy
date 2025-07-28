<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasActivated;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasPutBackOnTopOfPile;
use Domain\CoreGameLogic\Feature\Spielzug\Event\CardWasSkipped;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasEnded;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;

final readonly class AktionsCalculator
{
    private function __construct(
        private GameEvents $stream,
    ) {
    }

    public static function forStream(GameEvents $stream): self
    {
        return new self($stream);
    }

    // TODO maybe return an object with failed requirements?
    public function canPlayerAffordAction(PlayerId $playerId, ResourceChanges $cost): bool
    {
        $playerResources = PlayerState::getResourcesForPlayer($this->stream, $playerId);
        if (
            ($cost->guthabenChange->equals(0) || $cost->guthabenChange->value * -1 <= $playerResources->guthabenChange->value) &&
            $cost->zeitsteineChange * -1 <= $playerResources->zeitsteineChange &&
            $cost->bildungKompetenzsteinChange * -1 <= $playerResources->bildungKompetenzsteinChange &&
            $cost->freizeitKompetenzsteinChange * -1 <= $playerResources->freizeitKompetenzsteinChange
        ) {
            return true;
        }
        return false;
    }

    public function getEventsThisTurn(): GameEvents
    {
        return $this->stream->findAllAfterLastOfTypeOrNull(SpielzugWasEnded::class)
            ?? $this->stream->findAllAfterLastOfType(GameWasStarted::class);
    }

    public function hasPlayerSkippedACardThisRound(): bool
    {
        $eventsThisTurn = $this->getEventsThisTurn();
        return count($eventsThisTurn->findAllOfType(CardWasSkipped::class)) > 0;
    }

    public function hasPlayerPlayedACardOrPutOneBack(): bool
    {
        $eventsThisTurn = $this->getEventsThisTurn();
        $cardWasDiscarded = $eventsThisTurn->findLastOrNull(CardWasPutBackOnTopOfPile::class);
        $cardWasPlayed = $eventsThisTurn->findLastOrNull(CardWasActivated::class);

        if ($cardWasDiscarded !== null || $cardWasPlayed !== null) {
            return true;
        }

        return false;
    }

    function canPlayerAffordJobCard(PlayerId $player, JobCardDefinition $card): bool
    {
        $playerResources = PlayerState::getResourcesForPlayer($this->stream, $player);
        if (
            $card->requirements->zeitsteine <= $playerResources->zeitsteineChange &&
            $card->requirements->bildungKompetenzsteine <= $playerResources->bildungKompetenzsteinChange &&
            $card->requirements->freizeitKompetenzsteine <= $playerResources->freizeitKompetenzsteinChange
        ) {
            return true;
        }
        return false;
    }
}
