<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\GameReport\Dto;

use Domain\Definitions\Card\ValueObject\MoneyAmount;

final readonly class BreakdownEntry
{
    public function __construct(
        public MoneyCategory $category,
        public MoneyAmount $amount,
    ) {
    }
}
