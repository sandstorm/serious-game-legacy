<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\ZeitsteineForPlayer;
use Domain\CoreGameLogic\Feature\Konjunkturphase\ValueObject\CurrentYear;
use Domain\CoreGameLogic\Feature\Konjunkturphase\ValueObject\Zinssatz;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Konjunkturphase\Dto\KompetenzbereichDefinition;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphasenId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphaseTypeEnum;

final readonly class KonjunkturphaseWasChanged implements GameEventInterface
{
    /**
     * @param KompetenzbereichDefinition[] $kompetenzbereiche
     * @param ZeitsteineForPlayer[] $zeitsteineForPlayers
     */
    public function __construct(
        public KonjunkturphasenId      $id,
        public CurrentYear             $year,
        public KonjunkturphaseTypeEnum $type,
        public Zinssatz                $zinssatz,
        public array                   $kompetenzbereiche,
        public array                   $zeitsteineForPlayers,
    ) {
    }

    public static function fromArray(array $in): GameEventInterface
    {
        return new self(
            id: KonjunkturphasenId::create($in['id']),
            year: new CurrentYear($in['year']),
            type: KonjunkturphaseTypeEnum::fromString($in['type']),
            zinssatz: new Zinssatz($in['zinssatz']),
            kompetenzbereiche: array_map(
                static fn(array $kompetenzbereich) => KompetenzbereichDefinition::fromArray($kompetenzbereich),
                $in['kompetenzbereiche']
            ),
            zeitsteineForPlayers: array_map(fn($entry) => new ZeitsteineForPlayer(
                playerId: PlayerId::fromString($entry['playerId']),
                zeitsteine: $entry['zeitsteine']
            ), $in['zeitsteineForPlayers']),
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
            'zinssatz' => $this->zinssatz->jsonSerialize(),
            'kompetenzbereiche' => $this->kompetenzbereiche,
            'zeitsteineForPlayers' => $this->zeitsteineForPlayers,
        ];
    }
}
