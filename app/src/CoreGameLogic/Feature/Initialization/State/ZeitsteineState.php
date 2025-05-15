<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\State;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Dto\ValueObject\ResourceChanges;
use Domain\CoreGameLogic\Dto\ValueObject\Zeitsteine;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;

class ZeitsteineState
{
    public static function forPlayer(GameEvents $gameStream, PlayerId $playerId): Zeitsteine
    {
        return $gameStream->findAllOfType(ProvidesResourceChanges::class)->reduce(function (Zeitsteine $zeitsteine, ProvidesResourceChanges $event) use ($playerId) {
            $zeitsteineChange = $event->getResourceChanges($playerId)
                ->reduce(fn(ResourceChanges $accumulator, ResourceChanges $change) => $accumulator->accumulate($change), new ResourceChanges(guthabenChange: 0, zeitsteineChange: 0));
            return $zeitsteine->withChange($zeitsteineChange);

        }, new Zeitsteine(0));
    }
}
