<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Dto;

use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

readonly class TransactionEntry
{
    public function __construct(
        public PlayerTurn $playerTurn,
        public string $iconClass,
        public string $assetName,
        public int $amount,
        public MoneyAmount $price,
        public string $type,
        public int $holdingAfter,
    ) {
    }
}
