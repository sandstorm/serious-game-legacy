<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Dto\ValueObject\Lebensziel;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;

final readonly class LebenszielAuswaehlen implements CommandInterface
{
    // TODO selected lebensziel value object
    public function __construct(public PlayerId $playerId, public Lebensziel $lebensziel)
    {
    }
}
