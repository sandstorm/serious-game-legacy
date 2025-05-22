<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Player\State;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Dto\ValueObject\ResourceChanges;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;

class PlayerState
{
    public static function getResourcesForPlayer(GameEvents $stream, PlayerId $playerId): ResourceChanges
    {
        return $stream->findAllOfType(ProvidesResourceChanges::class)
            ->reduce(fn(ResourceChanges $accumulator, ProvidesResourceChanges $event) => $accumulator->accumulate($event->getResourceChanges($playerId)), new ResourceChanges());
    }

    /**
     * Returns the current amount of Zeitsteine available to the player.
     */
    public static function getZeitsteineForPlayer(GameEvents $stream, PlayerId $playerId): int
    {
        $accumulatedResourceChangesForPlayer = $stream->findAllOfType(ProvidesResourceChanges::class)
            ->reduce(fn(ResourceChanges $accumulator, ProvidesResourceChanges $event) => $accumulator->accumulate($event->getResourceChanges($playerId)), new ResourceChanges());

        return $accumulatedResourceChangesForPlayer->zeitsteineChange;
    }

    /**
     * Returns the current Guthaben of the player.
     */
    public static function getGuthabenForPlayer(GameEvents $stream, PlayerId $playerId): int
    {
        $accumulatedResourceChangesForPlayer = $stream->findAllOfType(ProvidesResourceChanges::class)
            ->reduce(fn(ResourceChanges $accumulator, ProvidesResourceChanges $event) => $accumulator->accumulate($event->getResourceChanges($playerId)), new ResourceChanges());

        return $accumulatedResourceChangesForPlayer->guthabenChange;
    }

    public static function getBildungsKompetenzsteine(GameEvents $stream, PlayerId $playerId): int
    {
        $accumulatedResourceChangesForPlayer = $stream->findAllOfType(ProvidesResourceChanges::class)
            ->reduce(fn(ResourceChanges $accumulator, ProvidesResourceChanges $event) => $accumulator->accumulate($event->getResourceChanges($playerId)), new ResourceChanges());

        return $accumulatedResourceChangesForPlayer->bildungKompetenzsteinChange;
    }

    public static function getFreizeitKompetenzsteine(GameEvents $stream, PlayerId $playerId): int
    {
        $accumulatedResourceChangesForPlayer = $stream->findAllOfType(ProvidesResourceChanges::class)
            ->reduce(fn(ResourceChanges $accumulator, ProvidesResourceChanges $event) => $accumulator->accumulate($event->getResourceChanges($playerId)), new ResourceChanges());

        return $accumulatedResourceChangesForPlayer->freizeitKompetenzsteinChange;
    }
}
