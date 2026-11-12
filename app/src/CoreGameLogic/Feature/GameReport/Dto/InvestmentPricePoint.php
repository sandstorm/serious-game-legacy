<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\GameReport\Dto;

use Domain\Definitions\Card\ValueObject\MoneyAmount;

/**
 * A new price of an investment. It is valid until the next price point.
 * $turnIndex is the number of Spielzüge played before the price was set: at the start of a year
 * or at the end of the Spielzug with that number.
 */
final readonly class InvestmentPricePoint
{
    public function __construct(
        public int $turnIndex,
        public bool $isYearStart,
        public MoneyAmount $price,
    ) {
    }
}
