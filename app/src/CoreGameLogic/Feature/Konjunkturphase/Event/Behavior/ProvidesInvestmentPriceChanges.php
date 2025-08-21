<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior;

use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\InvestmentPrice;

interface ProvidesInvestmentPriceChanges
{
    /** @return InvestmentPrice[] */
    public function getInvestmentPrices(): array; // of InvestmentPrice
}
