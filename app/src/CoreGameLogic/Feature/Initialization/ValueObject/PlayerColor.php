<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\ValueObject;

class PlayerColor implements \JsonSerializable
{

    public function __construct(public string $value)
    {
    }

    public function __toString(): string
    {
        return '[PlayerColor: '.$this->value.']';
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
