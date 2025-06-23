<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\PlayerId;

final readonly class RequestJobOffers implements CommandInterface
{
    public static function create(
        PlayerId $playerId,
    ): RequestJobOffers {
        return new self($playerId);
    }

    private function __construct(
        public PlayerId $playerId,
    ) {
    }

}
