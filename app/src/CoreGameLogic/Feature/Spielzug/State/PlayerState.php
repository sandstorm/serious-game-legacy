<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Initialization\Event\PreGameStarted;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;

class PlayerState
{
    public static function getResourcesForPlayer(GameEvents $stream, PlayerId $playerId): ResourceChanges
    {
        $accumulatedResources = $stream->findAllOfType(ProvidesResourceChanges::class)
            ->reduce(fn(ResourceChanges $accumulator, ProvidesResourceChanges $event) => $accumulator->accumulate($event->getResourceChanges($playerId)), new ResourceChanges());
        return new ResourceChanges(
            guthabenChange: $accumulatedResources->guthabenChange,
            zeitsteineChange: self::getZeitsteineForPlayer($stream, $playerId),
            bildungKompetenzsteinChange: $accumulatedResources->bildungKompetenzsteinChange,
            freizeitKompetenzsteinChange: $accumulatedResources->freizeitKompetenzsteinChange,
        );
    }

    /**
     * Returns the current amount of Zeitsteine available to the player.
     */
    public static function getZeitsteineForPlayer(GameEvents $stream, PlayerId $playerId): int
    {
        if (!in_array(needle: $playerId, haystack: $stream->findLast(PreGameStarted::class)->playerIds, strict: true)) {
            throw new \RuntimeException('Player ' . $playerId . ' does not exist', 1748432811);
        }
        $accumulatedResourceChangesForPlayer = $stream->findAllAfterLastOfType(KonjunkturphaseWasChanged::class)->findAllOfType(ProvidesResourceChanges::class)
            ->reduce(fn(ResourceChanges $accumulator, ProvidesResourceChanges $event) => $accumulator->accumulate($event->getResourceChanges($playerId)), new ResourceChanges());

        // TODO calculate zeitsteine for this Konjunkturphase from number of players and current Konjunkturphase-Auswirkungen
        return $accumulatedResourceChangesForPlayer->accumulate(new ResourceChanges(zeitsteineChange: +3))->zeitsteineChange;
    }

    /**
     * Returns the current Guthaben of the player.
     */
    public static function getGuthabenForPlayer(GameEvents $stream, PlayerId $playerId): int
    {
        // TODO: wenig Typisierung hier -> gibt alles plain values etc zurück.
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
