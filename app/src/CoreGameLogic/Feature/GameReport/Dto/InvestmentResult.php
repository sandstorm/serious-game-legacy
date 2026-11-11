<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\GameReport\Dto;

use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Investments\ValueObject\InvestmentId;

/**
 * What a player did with one investment over the whole game. The realized gain uses the average purchase price.
 */
final readonly class InvestmentResult
{
    public function __construct(
        public InvestmentId $investmentId,
        public MoneyAmount $invested,
        public MoneyAmount $proceeds,
        public MoneyAmount $realizedGain,
        public int $amountHeld,
        public MoneyAmount $valueHeld,
    ) {
    }
}
