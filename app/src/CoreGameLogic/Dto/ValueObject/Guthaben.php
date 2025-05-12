<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

readonly class Guthaben implements \JsonSerializable
{
    public function __construct(public int $value)
    {
    }

    public function __toString(): string
    {
        return '[Guthaben: '.$this->value.']';
    }

    public function jsonSerialize(): int
    {
        return $this->value;
    }

    public function withChange(int $guthabenChange)
    {
        return new self($this->value + $guthabenChange);
    }
}
