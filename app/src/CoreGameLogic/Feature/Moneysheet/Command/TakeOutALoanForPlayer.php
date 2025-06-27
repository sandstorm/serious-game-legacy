<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\ValueObject\Leitzins;
use Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanAmount;
use Domain\CoreGameLogic\PlayerId;

class TakeOutALoanForPlayer implements CommandInterface
{
    public static function create(
        PlayerId $playerId,
        string $intendedUse,
        LoanAmount $loanAmount,
        Leitzins $interestRate
    ): TakeOutALoanForPlayer {
        return new self($playerId, $intendedUse, $loanAmount, $interestRate);
    }

    private function __construct(
        public PlayerId $playerId,
        public string $intendedUse,
        public LoanAmount $loanAmount,
        public Leitzins $interestRate,
    ) {
    }

}
