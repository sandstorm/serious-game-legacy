<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior;

use Domain\CoreGameLogic\Feature\Spielzug\Dto\InvestmentAmountChanges;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Investments\ValueObject\InvestmentId;

interface ProvidesInvestmentAmountChanges
{
    public function getInvestmentAmountChanges(PlayerId $playerId, InvestmentId $investmentId): InvestmentAmountChanges;

    public function getInvestmentId(): InvestmentId;
}
