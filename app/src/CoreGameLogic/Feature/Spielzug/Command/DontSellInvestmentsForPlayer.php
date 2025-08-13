<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Domain\CoreGameLogic\PlayerId;

final readonly class DontSellInvestmentsForPlayer implements CommandInterface
{
    public static function create(
        PlayerId     $playerId,
        InvestmentId $investmentId,
    ): DontSellInvestmentsForPlayer
    {
        return new self(
            $playerId,
            $investmentId,
        );
    }

    private function __construct(
        public PlayerId     $playerId,
        public InvestmentId $investmentId,
    )
    {
    }

}
