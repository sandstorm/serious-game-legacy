<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\PlayerId;

final readonly class SetNameForPlayer implements CommandInterface
{
    public function __construct(public PlayerId $playerId, public string $name)
    {
    }
}
