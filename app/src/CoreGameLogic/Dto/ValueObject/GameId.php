<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

use Domain\CoreGameLogic\Dto\ValueObjectInterface;
use Neos\EventStore\Model\Event\StreamName;

readonly class GameId implements ValueObjectInterface
{
    public function __construct(public string $value)
    {
    }

    public function __toString(): string
    {
        return '[Game ID: ' . $this->value . ']';
    }

    public function equals(GameId $other): bool
    {
        return $this->value === $other->value;
    }

    public function streamName(): StreamName
    {
        return StreamName::fromString('game.'.$this->value);
    }
}
