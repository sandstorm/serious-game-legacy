<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\State;

use Domain\CoreGameLogic\Dto\ValueObject\Guthaben;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Dto\ValueObject\ResourceChanges;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;

class GuthabenState
{
    public static function forPlayer(GameEvents $gameStream, PlayerId $playerId): Guthaben
    {
        return $gameStream->findAllOfType(ProvidesResourceChanges::class)->reduce(function (Guthaben $guthaben, ProvidesResourceChanges $event) use ($playerId) {
            $guthabenChange = $event->getResourceChanges($playerId)
                ->reduce(fn(ResourceChanges $accumulator, ResourceChanges $change) => $accumulator->accumulate($change), new ResourceChanges(guthabenChange: 0));
            return $guthaben->withChange($guthabenChange);

        }, new Guthaben(0));
    }
}
