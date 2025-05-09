<?php
declare(strict_types=1);
namespace Domain\CoreGameLogic\Feature\Initialization\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;

class StartGame implements CommandInterface
{
    /**
     * @param PlayerId[] $playerOrdering
     */
    public function __construct(public array $playerOrdering)
    {
        foreach ($this->playerOrdering as $playerId) {
            assert($playerId instanceof PlayerId);
        }
    }
}
