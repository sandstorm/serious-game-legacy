<?php

declare(strict_types=1);

namespace Domain\Definitions\Cards\ValueObject;

final readonly class PileId implements \JsonSerializable
{
    public function __construct(public PileEnum $value)
    {
    }

    public function __toString(): string
    {
        return '[PileId: '.$this->value->value.']';
    }

    public static function fromString(string $string): self
    {
        return new self(PileEnum::from($string));
    }

    public function jsonSerialize(): string
    {
        return $this->value->value;
    }

    public function equals(PileId $other): bool
    {
        return $this->value === $other->value;
    }
}
