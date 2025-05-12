<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\Event;

use Domain\CoreGameLogic\Dto\ValueObject\Guthaben;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEventInterface;

final readonly class GuthabenInitialized implements GameEventInterface
{
    public function __construct(
        public PlayerId   $playerId,
        public Guthaben $initialGuthaben,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            initialGuthaben: new Guthaben($values['guthaben']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'guthaben' => $this->initialGuthaben,
        ];
    }
}
