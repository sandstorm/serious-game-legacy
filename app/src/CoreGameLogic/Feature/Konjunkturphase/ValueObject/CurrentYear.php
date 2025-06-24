<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\ValueObject;

readonly class CurrentYear implements \JsonSerializable
{
    public function __construct(public int $value)
    {
    }

    public function __toString(): string
    {
        return '[Jahr: '.$this->value.']';
    }

    public function jsonSerialize(): int
    {
        return $this->value;
    }

    public function equals(CurrentYear $other): bool
    {
        return $this->value === $other->value;
    }
}
