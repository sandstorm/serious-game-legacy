<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior;

use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

interface ProvidesStockPriceChanges
{
    public function getStockPrice(StockType $stockType): MoneyAmount;
}
