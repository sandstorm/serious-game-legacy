<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\PlayerRole\PlayerRole;

final readonly class RoleForPlayerWasSet implements GameEventInterface
{
    public function __construct(
        public PlayerId   $playerId,
        public PlayerRole $role,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['player']),
            role: PlayerRole::from($values['role']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->playerId,
            'role' => $this->role->value,
        ];
    }
}
