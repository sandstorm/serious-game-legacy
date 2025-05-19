<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\Event;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\Definitions\Lebensziel\LebenszielDefinition;

final readonly class LebenszielWasSelected implements GameEventInterface
{
    public function __construct(
        public PlayerId   $playerId,
        public LebenszielDefinition $lebensziel,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            lebensziel: LebenszielDefinition::fromArray($values['lebensziel']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'lebensziel' => $this->lebensziel->jsonSerialize(),
        ];
    }
}
