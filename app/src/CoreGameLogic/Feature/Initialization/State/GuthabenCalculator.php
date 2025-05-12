<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\State;

use Domain\CoreGameLogic\Dto\ValueObject\Guthaben;
use Domain\CoreGameLogic\Dto\ValueObject\GuthabenChange;
use Domain\CoreGameLogic\Dto\ValueObject\Modifier;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Dto\ValueObject\ResourceChange;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;

class GuthabenCalculator
{
    private function __construct(private GameEvents $stream)
    {

    }

    public static function forStream(GameEvents $stream): self
    {
        return new self($stream);
    }

    public function forPlayer(PlayerId $playerId): Guthaben
    {
        return $this->stream->findAllOfType(ProvidesResourceChanges::class)->reduce(function (Guthaben $guthaben, ProvidesResourceChanges $event) use ($playerId) {
            $guthabenChange = $event->getResourceChanges($playerId)
                ->filter(fn(ResourceChange $resourceChange) => $resourceChange instanceof GuthabenChange)
                ->reduce(fn(GuthabenChange $accumulator, GuthabenChange $change) => $accumulator->accumulate($change), new GuthabenChange(0));
            return $guthaben->withChange($guthabenChange);

        }, new Guthaben(0));
    }
}
