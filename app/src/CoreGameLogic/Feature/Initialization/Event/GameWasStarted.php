<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\PlayerId;

final readonly class GameWasStarted implements GameEventInterface
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

    public static function fromArray(array $values): GameEventInterface
    {
        $playerOrdering = array_map(fn (string $playerId) => PlayerId::fromString($playerId), $values['playerOrdering']);
        return new self($playerOrdering);
    }

    public function jsonSerialize(): array
    {
        return [
            'playerOrdering' => $this->playerOrdering,
        ];
    }
}
