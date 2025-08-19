<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Domain\CoreGameLogic\PlayerId;

final readonly class SellInvestmentsToAvoidInsolvenzForPlayer implements CommandInterface
{
    public static function create(
        PlayerId     $playerId,
        InvestmentId $investmentId,
        int          $amount
    ): SellInvestmentsToAvoidInsolvenzForPlayer {
        return new self(
            $playerId,
            $investmentId,
            $amount
        );
    }

    private function __construct(
        public PlayerId     $playerId,
        public InvestmentId $investmentId,
        public int          $amount
    ) {
    }

}
