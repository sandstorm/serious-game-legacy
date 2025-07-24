<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturphase\ValueObject;

readonly class Year implements \JsonSerializable
{
    public function __construct(public int $value)
    {
    }

    public function __toString(): string
    {
        return '[Year: '.$this->value.']';
    }

    public function jsonSerialize(): int
    {
        return $this->value;
    }

    public function equals(Year $other): bool
    {
        return $this->value === $other->value;
    }
}
