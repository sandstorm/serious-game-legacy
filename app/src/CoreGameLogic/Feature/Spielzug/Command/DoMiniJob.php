<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\PlayerId;

final readonly class DoMiniJob implements CommandInterface
{
    public static function create(
        PlayerId $playerId,
    ): DoMiniJob {
        return new self($playerId);
    }

    private function __construct(
        public PlayerId $player,
    ) {
    }

}
