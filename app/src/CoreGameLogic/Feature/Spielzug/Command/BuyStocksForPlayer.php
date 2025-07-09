<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

final readonly class BuyStocksForPlayer implements CommandInterface
{
    public static function create(
        PlayerId $playerId,
        StockType $stockType,
        MoneyAmount $price,
        int $amount
    ): BuyStocksForPlayer {
        return new self(
            $playerId,
            $stockType,
            $price,
            $amount
        );
    }

    private function __construct(
        public PlayerId $playerId,
        public StockType $stockType,
        public MoneyAmount $price,
        public int $amount
    ) {
    }

}
