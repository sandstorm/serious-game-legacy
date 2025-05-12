<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Dto\ValueObject\Guthaben;
use Domain\CoreGameLogic\Dto\ValueObject\GuthabenChange;

final readonly class InitPlayerGuthaben implements CommandInterface
{
    /**
     * @param Guthaben $initialGuthaben initial guthaben for all players
     */
    public function __construct(public GuthabenChange $initialGuthaben)
    {

    }
}
