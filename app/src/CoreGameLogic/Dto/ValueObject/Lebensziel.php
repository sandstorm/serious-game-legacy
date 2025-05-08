<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

readonly class Lebensziel implements \JsonSerializable
{
    public function __construct(public string $name)
    {
    }

    public function __toString(): string
    {
        return '[Lebensziel: '.$this->name.']';
    }

    public function jsonSerialize(): string
    {
        return $this->name;
    }
}
