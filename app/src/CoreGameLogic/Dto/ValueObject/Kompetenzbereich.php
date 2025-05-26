<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

use Domain\Definitions\Konjunkturphase\ValueObject\KompetenzbereichEnum;

readonly class Kompetenzbereich implements \JsonSerializable
{
    public function __construct(
        public KompetenzbereichEnum $name,
        public int $kompetenzsteine = 0,
    ) {
    }

    /**
     * @param array<string,mixed> $in
     */
    public static function fromArray(array $in): self
    {
        return new self(
            name: KompetenzbereichEnum::fromString($in['name']),
            kompetenzsteine: $in['kompetenzsteine'],
        );
    }

    public function __toString(): string
    {
        return '[Kompetenzbereich: '.$this->name->value.']';
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name->value,
            'kompetenzsteine' => $this->kompetenzsteine,
        ];
    }
}
