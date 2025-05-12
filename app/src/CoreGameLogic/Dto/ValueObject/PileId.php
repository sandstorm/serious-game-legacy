<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

use Domain\Definitions\Kompetenzbereich\Enum\KompetenzbereichEnum;

final readonly class PileId implements \JsonSerializable
{
    public function __construct(public KompetenzbereichEnum $value)
    {
    }

    public function __toString(): string
    {
        return '[PileId: '.$this->value->value.']';
    }

    public static function fromString(string $string): self
    {
        return new self(KompetenzbereichEnum::fromString($string));
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
