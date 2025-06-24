<?php

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\PlayerId;

final readonly class DoMiniJob implements GameEventInterface
{
    public function __construct(
        public PlayerId $playerId,
        public CardId $cardId,
    )
    {

    }

    public static function fromArray(array $values): GameEventInterface
    {
        // TODO: Implement fromArray() method.
        return[];
    }

    public function jsonSerialize(): array
    {
        // TODO: Implement jsonSerialize() method.
        return[];
    }
}
