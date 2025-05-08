<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\Event;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEventInterface;

// TODO: maybe also rename to "update player orddering // auch für Pausieren etc
final readonly class InitializePlayerOrdering implements GameEventInterface
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
        $playerOrdering = array_map(fn(string $playerId) => new PlayerId($playerId), $values['playerOrdering']);
        return new self($playerOrdering);
    }

    public function jsonSerialize(): array
    {
        return [
            'playerOrdering' => $this->playerOrdering,
        ];
    }
}
