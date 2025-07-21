<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Lebensziel\Dto\LebenszielDefinition;

final readonly class LebenszielWasSelected implements GameEventInterface
{
    public function __construct(
        public PlayerId   $playerId,
        public LebenszielDefinition $lebenszielDefinition,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            lebenszielDefinition: LebenszielDefinition::fromArray($values['lebenszielDefinition']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'playerId' => $this->playerId,
            'lebenszielDefinition' => $this->lebenszielDefinition->jsonSerialize(),
        ];
    }
}
