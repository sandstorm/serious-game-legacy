<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;
use Domain\CoreGameLogic\PlayerId;

final readonly class SellStocksForPlayer implements CommandInterface
{
    public static function create(
        PlayerId $playerId,
        StockType $stockType,
        int $amount
    ): SellStocksForPlayer {
        return new self(
            $playerId,
            $stockType,
            $amount
        );
    }

    private function __construct(
        public PlayerId $playerId,
        public StockType $stockType,
        public int $amount
    ) {
    }

}
