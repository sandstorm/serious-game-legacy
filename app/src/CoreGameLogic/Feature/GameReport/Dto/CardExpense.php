<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\GameReport\Dto;

use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

final readonly class CardExpense
{
    public function __construct(
        public string $title,
        public MoneyAmount $amount,
        public Year $year,
    ) {
    }
}
