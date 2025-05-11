<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\Event;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEventInterface;

final readonly class PreGameStarted implements GameEventInterface
{
    /**
     * @param PlayerId[] $playerIds
     */
    public function __construct(public array $playerIds)
    {
        foreach ($this->playerIds as $playerId) {
            assert($playerId instanceof PlayerId, 'Player ID must be an instance of PlayerId');
        }
    }

    public static function fromArray(array $values): GameEventInterface
    {
        $playerIds = array_map(fn (string $playerId) => PlayerId::fromString($playerId), $values['playerIds']);
        return new self($playerIds);
    }

    public function jsonSerialize(): array
    {
        return [
            'playerIds' => $this->playerIds,
        ];
    }
}
