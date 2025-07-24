<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\PlayerId;

final readonly class CardWasPutBackOnTopOfPile implements GameEventInterface
{
    public function __construct(
        public PlayerId   $playerId,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
        ];
    }

    public function getPlayerId(): PlayerId
    {
        return $this->playerId;
    }
}
