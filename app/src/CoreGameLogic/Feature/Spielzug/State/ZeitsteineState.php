<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\Zeitsteine;
use Domain\CoreGameLogic\PlayerId;

class ZeitsteineState
{
    public static function forPlayer(GameEvents $gameStream, PlayerId $playerId): Zeitsteine
    {
        return $gameStream->findAllOfType(ProvidesResourceChanges::class)->reduce(function (Zeitsteine $zeitsteine, ProvidesResourceChanges $event) use ($playerId) {
            return $zeitsteine->withChange($event->getResourceChanges($playerId));
        }, new Zeitsteine(0));
    }
}
