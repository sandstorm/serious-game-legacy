<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

readonly class Lebensziel implements \JsonSerializable
{
    // TODO phases, goals, etc
    public function __construct(public string $value)
    {
    }

    public function __toString(): string
    {
        return '[Lebensziel: '.$this->value.']';
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
