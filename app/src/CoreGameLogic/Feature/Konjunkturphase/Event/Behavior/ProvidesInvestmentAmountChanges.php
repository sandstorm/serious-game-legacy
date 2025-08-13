<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior;

use Domain\CoreGameLogic\Feature\Spielzug\Dto\InvestmentAmountChanges;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Domain\CoreGameLogic\PlayerId;

interface ProvidesInvestmentAmountChanges
{
    public function getInvestmentAmountChanges(PlayerId $playerId, InvestmentId $investmentId): InvestmentAmountChanges;
}
