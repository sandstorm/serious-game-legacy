<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\PlayerRole\PlayerRole;

final readonly class SetRoleForPlayer implements CommandInterface
{
    public function __construct(public PlayerId $playerId, public PlayerRole $role)
    {
    }
}
