<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Event;

use Domain\CoreGameLogic\Dto\ValueObject\Leitzins;
use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\ValueObject\CurrentYear;
use Domain\Definitions\Konjunkturphase\Dto\KompetenzbereichDefinition;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphasenId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphaseTypeEnum;

final readonly class KonjunkturphaseWasChanged implements GameEventInterface
{
    /**
     * @param KompetenzbereichDefinition[] $kompetenzbereiche
     */
    public function __construct(
        public KonjunkturphasenId      $id,
        public CurrentYear             $year,
        public KonjunkturphaseTypeEnum $type,
        public Leitzins                $leitzins,
        public array                   $kompetenzbereiche
    )
    {
    }

    public static function fromArray(array $in): GameEventInterface
    {
        return new self(
            id: KonjunkturphasenId::create($in['id']),
            year: new CurrentYear($in['year']),
            type: KonjunkturphaseTypeEnum::fromString($in['type']),
            leitzins: new Leitzins($in['leitzins']),
            kompetenzbereiche: array_map(
                static fn(array $kompetenzbereich) => KompetenzbereichDefinition::fromArray($kompetenzbereich),
                $in['kompetenzbereiche']
            )
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id->jsonSerialize(),
            'year' => $this->year->jsonSerialize(),
            'type' => $this->type,
            'leitzins' => $this->leitzins->jsonSerialize(),
            'kompetenzbereiche' => $this->kompetenzbereiche
        ];
    }
}
