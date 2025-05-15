<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Dto\ValueObject\LebenszielId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\Definitions\Lebensziel\Model\LebenszielDefinition;

final readonly class LebenszielAuswaehlen implements CommandInterface
{
    public function __construct(
        public PlayerId $playerId,
        public LebenszielId $lebensziel,
    ) {
    }
}
