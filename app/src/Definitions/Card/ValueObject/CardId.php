<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\ValueObject;

final readonly class CardId implements \JsonSerializable
{
    public function __construct(public string $value)
    {
    }

    public function __toString(): string
    {
        return '[CardId: '.$this->value.']';
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }

    public function equals(CardId $other): bool
    {
        return $this->value === $other->value;
    }
}
