<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

use Domain\CoreGameLogic\Dto\Enum\Kompetenzbereiche;

readonly class Kategorie implements \JsonSerializable
{
    public function __construct(
        public Kompetenzbereiche $name,
        public int $zeitSlots = 0,
    ) {
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
