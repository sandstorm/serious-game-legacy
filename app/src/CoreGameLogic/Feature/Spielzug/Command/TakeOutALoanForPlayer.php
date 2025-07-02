<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

class TakeOutALoanForPlayer implements CommandInterface
{
    public static function create(
        PlayerId $playerId,
        string $intendedUse,
        MoneyAmount $loanAmount,
        MoneyAmount $totalRepayment,
        MoneyAmount $repaymentPerKonjunkturphase,
    ): TakeOutALoanForPlayer {
        return new self($playerId, $intendedUse, $loanAmount, $totalRepayment, $repaymentPerKonjunkturphase);
    }

    private function __construct(
        public PlayerId $playerId,
        public string $intendedUse,
        public MoneyAmount $loanAmount,
        public MoneyAmount $totalRepayment,
        public MoneyAmount $repaymentPerKonjunkturphase,
    ) {
    }

}
