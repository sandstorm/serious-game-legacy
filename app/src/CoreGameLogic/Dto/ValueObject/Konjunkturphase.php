<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

use Domain\CoreGameLogic\Feature\Konjunkturphase\ValueObject\CurrentYear;
use Domain\Definitions\Konjunkturphase\Dto\KompetenzbereichDefinition;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphaseTypeEnum;

readonly class Konjunkturphase implements \JsonSerializable
{
    /**
     * @param int $id
     * @param CurrentYear $year
     * @param KonjunkturphaseTypeEnum $type
     * @param Leitzins $leitzins
     * @param KompetenzbereichDefinition[] $kompetenzbereiche
     */
    public function __construct(
        public int                     $id,
        public CurrentYear             $year,
        public KonjunkturphaseTypeEnum $type,
        public Leitzins                $leitzins,
        public array                   $kompetenzbereiche
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
            year: new CurrentYear($in['year']),
            type: KonjunkturphaseTypeEnum::fromString($in['type']),
            leitzins: new Leitzins($in['leitzins']),
            kompetenzbereiche: array_map(
                static fn(array $kompetenzbereich) => KompetenzbereichDefinition::fromArray($kompetenzbereich),
                $in['kompetenzbereiche']
            )
        );
    }

    public function __toString(): string
    {
        return '[Konjunkturphase: ' . $this->type->value . ']';
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'year' => $this->year,
            'type' => $this->type,
            'leitzins' => $this->leitzins,
            'kompetenzbereiche' => $this->kompetenzbereiche
        ];
    }
}
