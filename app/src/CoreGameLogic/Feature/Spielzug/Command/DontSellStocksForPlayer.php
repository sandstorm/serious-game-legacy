<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;
use Domain\CoreGameLogic\PlayerId;

final readonly class DontSellStocksForPlayer implements CommandInterface
{
    public static function create(
        PlayerId $playerId,
        StockType $stockType,
    ): DontSellStocksForPlayer {
        return new self(
            $playerId,
            $stockType,
        );
    }

    private function __construct(
        public PlayerId $playerId,
        public StockType $stockType,
    ) {
    }

}
