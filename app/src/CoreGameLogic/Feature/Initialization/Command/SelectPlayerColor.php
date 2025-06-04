<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Feature\Initialization\ValueObject\PlayerColor;
use Domain\CoreGameLogic\PlayerId;

final readonly class SelectPlayerColor implements CommandInterface
{
    public function __construct(
        public PlayerId $playerId,
        public ?PlayerColor $playerColor,
    ) {
    }
}
