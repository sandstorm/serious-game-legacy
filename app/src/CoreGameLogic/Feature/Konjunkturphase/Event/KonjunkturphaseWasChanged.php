<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\StockPrice;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\ZeitsteineForPlayer;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\ProvidesStockPriceChanges;
use Domain\CoreGameLogic\Feature\Konjunkturphase\ValueObject\Year;
use Domain\CoreGameLogic\Feature\Konjunkturphase\ValueObject\Zinssatz;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Konjunkturphase\Dto\KompetenzbereichDefinition;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphasenId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphaseTypeEnum;

final readonly class KonjunkturphaseWasChanged implements GameEventInterface, ProvidesStockPriceChanges
{
    /**
     * @param KompetenzbereichDefinition[] $kompetenzbereiche
     * @param ZeitsteineForPlayer[] $zeitsteineForPlayers
     * @param StockPrice[] $stockPrices
     */
    public function __construct(
        public KonjunkturphasenId      $id,
        public Year                    $year,
        public KonjunkturphaseTypeEnum $type,
        public Zinssatz                $zinssatz,
        public array                   $kompetenzbereiche,
        public array                   $zeitsteineForPlayers,
        public array                   $stockPrices
    )
    {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            id: KonjunkturphasenId::create($values['id']),
            year: new Year($values['year']),
            type: KonjunkturphaseTypeEnum::fromString($values['type']),
            zinssatz: new Zinssatz($values['zinssatz']),
            kompetenzbereiche: array_map(
                static fn(array $kompetenzbereich) => KompetenzbereichDefinition::fromArray($kompetenzbereich),
                $values['kompetenzbereiche']
            ),
            zeitsteineForPlayers: array_map(fn($entry) => new ZeitsteineForPlayer(
                playerId: PlayerId::fromString($entry['playerId']),
                zeitsteine: $entry['zeitsteine']
            ), $values['zeitsteineForPlayers']),
            stockPrices: array_map(
                static fn ($stockPrice) => StockPrice::fromArray($stockPrice),
                $values['stockPrices']
            ),
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
            'stockPrices' => $this->stockPrices,
        ];
    }

    public function getStockPrice(StockType $stockType): MoneyAmount
    {
        foreach ($this->stockPrices as $stockPrice) {
            if ($stockPrice->stockType === $stockType) {
                return $stockPrice->sharePrice;
            }
        }
        throw new \DomainException('Stock price not found for stock type: ' . $stockType->value, 1752584032);
    }
}
