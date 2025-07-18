<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\ProvidesStockAmountChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\StockAmountChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

class StocksWereSoldForPlayer implements GameEventInterface, ProvidesResourceChanges, ProvidesStockAmountChanges
{
    /**
     * @param PlayerId $playerId
     * @param StockType $stockType
     * @param MoneyAmount $sharePrice
     * @param int $amount
     */
    public function __construct(
        public PlayerId    $playerId,
        public StockType   $stockType,
        public MoneyAmount $sharePrice,
        public int         $amount,
    )
    {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['player']),
            stockType: StockType::fromString($values['stockType']),
            sharePrice: new MoneyAmount($values['sharePrice']),
            amount: $values['amount'],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->playerId,
            'stockType' => $this->stockType->value,
            'sharePrice' => $this->sharePrice->value,
            'amount' => $this->amount,
        ];
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($this->playerId->equals($playerId)) {
            return new ResourceChanges(
                guthabenChange: new MoneyAmount($this->sharePrice->value * $this->amount),
            );
        }
        return new ResourceChanges();
    }

    public function getPlayerId(): PlayerId
    {
        return $this->playerId;
    }

    public function getStockAmountChanges(PlayerId $playerId, StockType $stockType): StockAmountChanges
    {
        if ($this->playerId->equals($playerId)) {
            return new StockAmountChanges(
                amountChange: $this->amount * -1
            );
        }
        return new StockAmountChanges();
    }
}
