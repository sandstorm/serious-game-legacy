<?php

declare(strict_types=1);

namespace Domain\Definitions\Lebensziel\Model;


use Domain\Definitions\Kompetenzbereich\Enum\KompetenzbereichEnum;

readonly class LebenszielKompetenzbereichDefinition
{
    public function __construct(
        public KompetenzbereichEnum $name,
        public int $slots,
        public int $placed = 0,
    ) {
    }

    /**
     * @param array{name: string, slots: int, placed: int} $values
     * @return self
     */
    public static function fromArray(array $values): self
    {
        return new self(
            name: KompetenzbereichEnum::fromString($values['name']),
            slots: $values['slots'],
            placed: $values['placed'],
        );
    }

    /**
     * @return array{name: string, slots: int, placed: int}
     */
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
