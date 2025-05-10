<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\State;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\LebenszielChosen;

class LebenszielAccessor
{
    private function __construct(private GameEvents $stream)
    {

    }

    public static function forStream(GameEvents $stream): self
    {
        return new self($stream);
    }

    /**
     * @deprecated  please remove me and use PreGameState::lebenszielForPlayer instead.
     */
    public function forPlayer(PlayerId $playerId): ?LebenszielChosen
    {
        return $this->stream->findAllOfType(LebenszielChosen::class)->filter(function ($event) use ($playerId) {
            return $event->playerId->equals($playerId);
        })[0] ?? null;
    }
}
