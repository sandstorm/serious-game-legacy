<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

use Domain\CoreGameLogic\Dto\Enum\KonjunkturzyklusTypeEnum;

readonly class Konjunkturzyklus implements \JsonSerializable
{
    /**
     * @param KonjunkturzyklusTypeEnum $type
     * @param Leitzins $leitzins
     * @param Kompetenzbereich[] $kompetenzbereiche
     */
    public function __construct(
        public int                      $id,
        public KonjunkturzyklusTypeEnum $type,
        public Leitzins                 $leitzins,
        public array                    $kompetenzbereiche
    )
    {
    }

    /**
     * @param array<string,mixed> $in
     */
    public static function fromArray(array $in): self
    {
        return new self(
            id: $in['id'],
            type: KonjunkturzyklusTypeEnum::fromString($in['type']),
            leitzins: new Leitzins($in['leitzins']),
            kompetenzbereiche: array_map(
                static fn(array $kompetenzbereich) => Kompetenzbereich::fromArray($kompetenzbereich),
                $in['kompetenzbereiche']
            )
        );
    }

    public function __toString(): string
    {
        return '[Konjunkturzyklus: ' . $this->type->value . ']';
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'leitzins' => $this->leitzins,
            'kompetenzbereiche' => $this->kompetenzbereiche
        ];
    }
}
