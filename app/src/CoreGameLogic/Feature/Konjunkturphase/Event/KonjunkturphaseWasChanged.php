<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\StockPrice;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\ProvidesStockPriceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphasenId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphaseTypeEnum;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

final readonly class KonjunkturphaseWasChanged implements GameEventInterface, ProvidesStockPriceChanges
{
    /**
     * @param StockPrice[] $stockPrices
     */
    public function __construct(
        public KonjunkturphasenId      $id,
        public Year                    $year,
        public KonjunkturphaseTypeEnum $type,
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
            stockPrices: array_map(
                static fn($stockPrice) => StockPrice::fromArray($stockPrice),
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
