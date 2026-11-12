<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\GameReport\Dto;

use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

/**
 * A purchase or sale of an investment, $turnIndex is the number of Spielzüge played before the trade.
 */
final readonly class InvestmentTrade
{
    public function __construct(
        public PlayerId $playerId,
        public int $turnIndex,
        public bool $isSale,
        public int $amount,
        public MoneyAmount $price,
    ) {
    }
}
