<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Dto;

use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

readonly final class StockData
{
    public function __construct(
        public StockType $stockType,
        public MoneyAmount $price,
        public int $amount,
        public MoneyAmount $totalValue,
        public MoneyAmount $totalDividend,
    ) {
    }

}
