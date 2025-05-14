<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\Event;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\Definitions\Lebensziel\Model\Lebensziel;

final readonly class LebenszielChosen implements GameEventInterface
{
    public function __construct(
        public PlayerId   $playerId,
        public Lebensziel $lebensziel,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['playerId']),
            lebensziel: Lebensziel::fromArray($values['lebensziel']),
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
