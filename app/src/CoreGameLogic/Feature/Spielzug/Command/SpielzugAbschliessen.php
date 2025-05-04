<?php

namespace Domain\CoreGameLogic\Feature\Spielzug\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;

final readonly class SpielzugAbschliessen implements CommandInterface
{
    public function __construct(public PlayerId $playerId)
    {
    }
}
