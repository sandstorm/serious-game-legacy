<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\ValueObject;

final readonly class Gehalt implements \JsonSerializable
{
    public function __construct(public int $value)
    {
    }

    public function __toString(): string
    {
        return '[Gehalt: '.$this->value.']';
    }


    public function jsonSerialize(): int
    {
        return $this->value;
    }
}
