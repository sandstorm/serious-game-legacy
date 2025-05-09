<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\GameState;

use Domain\CoreGameLogic\Dto\Event\InitLebenszielEvent;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;

class LebenszielAccessor
{
    private function __construct(private GameEvents $stream)
    {

    }

    public static function forStream(GameEvents $stream): self
    {
        return new self($stream);
    }

    public function forPlayer(PlayerId $playerId): ?InitLebenszielEvent
    {
        return $this->stream->findAllOfType(InitLebenszielEvent::class)->filter(function($event) use ($playerId) {
            return $event->player->equals($playerId);
        })[0] ?? null;
    }
}
