<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Initialization\ValueObject\PlayerColor;
use Domain\CoreGameLogic\PlayerId;

final readonly class PlayerColorWasSelected implements GameEventInterface
{
    public function __construct(
        public PlayerId   $playerId,
        public PlayerColor $playerColor,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            playerColor: PlayerColor::fromString($values['playerColor']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'playerColor' => $this->playerColor,
        ];
    }
}
