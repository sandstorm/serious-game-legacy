<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanId;
use Domain\CoreGameLogic\PlayerId;

final readonly class RepayLoanForPlayerInCaseOfInsolvenz implements CommandInterface
{
    public static function create(
        PlayerId $playerId,
        LoanId $loanId
    ): RepayLoanForPlayerInCaseOfInsolvenz
    {
        return new self($playerId, $loanId);
    }

    private function __construct(
        public PlayerId $playerId,
        public LoanId $loanId
    ) {
    }
}
