<?php

namespace Domain\CoreGameLogic\Dto\Event;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;

// TODO: maybe also rename to "update player orddering // auch für Pausieren etc
final readonly class InitializePlayerOrdering
{

    /**
     * @param PlayerId[] $playerOrdering
     */
    public function __construct(public array $playerOrdering)
    {
        foreach ($this->playerOrdering as $playerId) {
            assert($playerId instanceof PlayerId);
        }
    }
}
