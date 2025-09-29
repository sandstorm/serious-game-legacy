<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\ImmobilieId;
use Domain\CoreGameLogic\PlayerId;

final readonly class SellImmobilieForPlayerToAvoidInsolvenz implements CommandInterface
{
    public static function create(
        PlayerId $playerId,
        ImmobilieId $immobilieId,
    ): SellImmobilieForPlayerToAvoidInsolvenz {
        return new self(
            $playerId,
            $immobilieId
        );
    }

    private function __construct(
        public PlayerId $playerId,
        public ImmobilieId $immobilieId,
    ) {}
}
