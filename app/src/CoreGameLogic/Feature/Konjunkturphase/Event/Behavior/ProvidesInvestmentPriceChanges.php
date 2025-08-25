<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior;

use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\InvestmentPrice;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

interface ProvidesInvestmentPriceChanges
{
    public function getInvestmentPrice(InvestmentId $investmentId): MoneyAmount;
    /** @return InvestmentPrice[] */
    public function getInvestmentPrices(): array; // of InvestmentPrice
}
