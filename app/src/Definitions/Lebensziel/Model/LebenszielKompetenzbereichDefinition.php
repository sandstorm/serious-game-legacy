<?php

namespace Domain\Definitions\Lebensziel\Model;

use Domain\CoreGameLogic\Dto\Enum\KompetenzbereichEnum;
use Domain\CoreGameLogic\Dto\ValueObject\ResourceChanges;

readonly class LebenszielKompetenzbereichDefinition
{
    public function __construct(
        public KompetenzbereichEnum $name,
        public int $slots,
        public int $placed = 0,
    ) {
    }

    public static function fromArray(mixed $values): self
    {
        return new self(
            name: KompetenzbereichEnum::fromString($values['name']),
            slots: $values['slots'],
            placed: $values['placed'],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name->value,
            'slots' => $this->slots,
            'placed' => $this->placed,
        ];
    }

    public function accumulate(int $changePlaced): self
    {
        return new self(
            name: $this->name,
            slots: $this->slots,
            placed: $this->placed + $changePlaced,
        );
    }
}
