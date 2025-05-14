<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\State;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\LebenszielChosen;
use Domain\Definitions\Lebensziel\Model\Lebensziel;

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
    public function forPlayer(PlayerId $playerId): ?Lebensziel
    {
        $lebensziel = $this->stream->findAllOfType(LebenszielChosen::class)->filter(function ($event) use ($playerId) {
            return $event->playerId->equals($playerId);
        })[0]->lebensziel ?? null;
        if ($lebensziel === null) {
            return null;
        }
        return $lebensziel;
    }
}
