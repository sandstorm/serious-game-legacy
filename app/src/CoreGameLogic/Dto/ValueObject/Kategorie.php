<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

readonly class Kategorie implements \JsonSerializable
{
    public function __construct(
        public string $name,
        public int $zeitSlots = 0,
    ) {
    }

    public function __toString(): string
    {
        return '[Kategorie: '.$this->name.']';
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'zeitSlots' => $this->zeitSlots,
        ];
    }
}
