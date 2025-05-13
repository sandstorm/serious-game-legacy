<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

use Domain\CoreGameLogic\Dto\Enum\Kompetenzbereich;

readonly class Kategorie implements \JsonSerializable
{
    public function __construct(
        public Kompetenzbereich $name,
        public int              $zeitSlots = 0,
    ) {
    }

    /**
     * @param array<string,mixed> $in
     */
    public static function fromArray(array $in): self
    {
        return new self(
            name: Kompetenzbereich::fromString($in['name']),
            zeitSlots: $in['zeitSlots'],
        );
    }

    public function __toString(): string
    {
        return '[Kategorie: '.$this->name->value.']';
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name->value,
            'zeitSlots' => $this->zeitSlots,
        ];
    }
}
