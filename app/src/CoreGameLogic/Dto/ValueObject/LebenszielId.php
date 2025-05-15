<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

readonly class LebenszielId implements \JsonSerializable
{
    public function __construct(public int $value)
    {
    }

    public function __toString(): string
    {
        return '[LebenszielId: '.$this->value.']';
    }

    public function jsonSerialize(): int
    {
        return $this->value;
    }
}
