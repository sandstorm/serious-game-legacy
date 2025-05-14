<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\Definitions\Lebensziel\Model\Lebensziel;

final readonly class LebenszielAuswaehlen implements CommandInterface
{
    // TODO selected lebensziel value object
    public function __construct(public PlayerId $playerId, public Lebensziel $lebensziel)
    {
    }
}
