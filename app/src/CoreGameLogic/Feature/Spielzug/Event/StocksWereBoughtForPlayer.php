<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

class StocksWereBoughtForPlayer implements GameEventInterface, ProvidesResourceChanges
{
    public function __construct(
        public PlayerId    $playerId,
        public StockType   $stockType,
        public MoneyAmount $price,
        public int         $amount,
    )
    {
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            playerId: PlayerId::fromString($values['player']),
            stockType: StockType::fromString($values['stockType']),
            price: new MoneyAmount($values['price']),
            amount: $values['amount'],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->playerId,
            'stockType' => $this->stockType->value,
            'price' => $this->price->value,
            'amount' => $this->amount,
        ];
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($this->playerId->equals($playerId)) {
            return new ResourceChanges(
                guthabenChange: new MoneyAmount(-1 * ($this->price->value * $this->amount)),
            );
        }
        return new ResourceChanges();
    }
}
