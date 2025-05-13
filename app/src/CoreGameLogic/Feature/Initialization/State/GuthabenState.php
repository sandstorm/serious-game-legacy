<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\State;

use Domain\CoreGameLogic\Dto\ValueObject\Guthaben;
use Domain\CoreGameLogic\Dto\ValueObject\GuthabenChange;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Dto\ValueObject\ResourceChange;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;

class GuthabenState
{
    public static function forPlayer(GameEvents $gameStream, PlayerId $playerId): Guthaben
    {
        return $gameStream->findAllOfType(ProvidesResourceChanges::class)->reduce(function (Guthaben $guthaben, ProvidesResourceChanges $event) use ($playerId) {
            $guthabenChange = $event->getResourceChanges($playerId)
                ->filter(fn(ResourceChange $resourceChange) => $resourceChange instanceof GuthabenChange)
                /**@phpstan-ignore argument.type */
                ->reduce(fn(GuthabenChange $accumulator, GuthabenChange $change) => $accumulator->accumulate($change), new GuthabenChange(0));
            return $guthaben->withChange($guthabenChange);

        }, new Guthaben(0));
    }
}
