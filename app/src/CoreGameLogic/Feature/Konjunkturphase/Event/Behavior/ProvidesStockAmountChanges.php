<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior;

use Domain\CoreGameLogic\Feature\Spielzug\Dto\StockAmountChanges;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;
use Domain\CoreGameLogic\PlayerId;

interface ProvidesStockAmountChanges
{
    public function getStockAmountChanges(PlayerId $playerId, StockType $stockType): StockAmountChanges;
}
