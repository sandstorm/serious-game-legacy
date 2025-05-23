<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;

final readonly class EndSpielzug implements CommandInterface
{
    public function __construct(public PlayerId $player)
    {
    }
}
