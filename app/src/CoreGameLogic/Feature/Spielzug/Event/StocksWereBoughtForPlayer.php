<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\StockPrice;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\ProvidesStockAmountChanges;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\ProvidesStockPriceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LogEntry;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\StockAmountChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\Loggable;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ZeitsteinAktion;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

class StocksWereBoughtForPlayer implements GameEventInterface, ProvidesResourceChanges, ProvidesStockPriceChanges, ZeitsteinAktion, ProvidesStockAmountChanges, Loggable
{
    /**
     * @param PlayerId $playerId
     * @param StockType $stockType
     * @param MoneyAmount $sharePrice
     * @param int $amount
     * @param StockPrice[] $stockPrices
     * @param ResourceChanges $resourceChanges
     */
    public function __construct(
        public PlayerId $playerId,
        public StockType $stockType,
        public MoneyAmount $sharePrice,
        public int $amount,
        public array $stockPrices,
        public ResourceChanges $resourceChanges,
    ) {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['player']),
            stockType: StockType::from($values['stockType']),
            sharePrice: new MoneyAmount($values['sharePrice']),
            amount: $values['amount'],
            stockPrices: array_map(
                static fn($stockPrice) => StockPrice::fromArray($stockPrice),
                $values['stockPrices']
            ),
            resourceChanges: ResourceChanges::fromArray($values['resourceChanges']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->playerId,
            'stockType' => $this->stockType->value,
            'sharePrice' => $this->sharePrice->value,
            'amount' => $this->amount,
            'stockPrices' => $this->stockPrices,
            'resourceChanges' => $this->resourceChanges,
        ];
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($this->playerId->equals($playerId)) {
            return $this->resourceChanges;
        }
        return new ResourceChanges();
    }

    public function getStockPrice(StockType $stockType): MoneyAmount
    {
        foreach ($this->stockPrices as $stockPrice) {
            if ($stockPrice->stockType === $stockType) {
                return $stockPrice->sharePrice;
            }
        }
        throw new \RuntimeException('Stock price not found for stock type: ' . $stockType->value, 1752584261);
    }

    public function getCategoryId(): CategoryId
    {
        return CategoryId::INVESTITIONEN;
    }

    public function getPlayerId(): PlayerId
    {
        return $this->playerId;
    }

    public function getNumberOfZeitsteinslotsUsed(): int
    {
        return 1; // Buying stocks uses one Zeitsteinslot
    }

    public function getStockAmountChanges(PlayerId $playerId, StockType $stockType): StockAmountChanges
    {
        if ($this->playerId->equals($playerId) && $this->stockType === $stockType) {
            return new StockAmountChanges(
                amountChange: $this->amount
            );
        }
        return new StockAmountChanges();
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(
            playerId: $this->playerId,
            text: "kauft " . $this->amount . " '" . $this->stockType->toPrettyString() . "' Aktien",
            resourceChanges: $this->resourceChanges,
        );
    }
}
