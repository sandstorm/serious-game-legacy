<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Dto;

use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

readonly final class InvestmentData
{
    public function __construct(
        public InvestmentId $investmentId,
        public MoneyAmount  $price,
        public int          $amount,
        public MoneyAmount  $totalValue,
        public MoneyAmount  $totalDividend,
    ) {
    }

}
