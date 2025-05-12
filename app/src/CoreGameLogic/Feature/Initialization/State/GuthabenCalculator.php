<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\State;

use Domain\CoreGameLogic\Dto\ValueObject\Guthaben;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GuthabenInitialized;

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
        return $this->stream->findAllOfType(GuthabenInitialized::class)->filter(function ($event) use ($playerId) {
            return $event->playerId->equals($playerId);
        })[0]->initialGuthaben ?? new Guthaben(0);
    }
}
