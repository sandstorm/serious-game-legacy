<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Insurance\ValueObject\InsuranceId;

final readonly class ConcludeInsuranceForPlayer implements CommandInterface
{
    public static function create(PlayerId $playerId, InsuranceId $insuranceId): ConcludeInsuranceForPlayer
    {
        return new self($playerId, $insuranceId);
    }

    private function __construct(
        public PlayerId $playerId,
        public InsuranceId $insuranceId,
    ) {
    }
}
