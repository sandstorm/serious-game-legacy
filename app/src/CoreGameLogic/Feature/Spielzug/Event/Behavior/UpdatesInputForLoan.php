<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior;

use Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanId;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LoanData;
use Domain\CoreGameLogic\PlayerId;

/**
 * This interface is applied on GameEvents which change the Input value for a Loan.
 */
interface UpdatesInputForLoan
{
    public function getPlayerId(): PlayerId;
    public function getLoanId(): LoanId;
    public function getLoanData(): LoanData;
}
