<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\PlayerId;

final readonly class TakeOutALoanForPlayer implements CommandInterface
{
    public static function create(
        PlayerId $playerId,
        int|null $loanAmount
    ): TakeOutALoanForPlayer
    {
        return new self($playerId, $loanAmount);
    }

    private function __construct(
        public PlayerId $playerId,
        public int|null $loanAmount
    ) {
    }
}
