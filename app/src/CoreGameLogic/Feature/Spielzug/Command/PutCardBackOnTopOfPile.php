<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

final readonly class PutCardBackOnTopOfPile implements CommandInterface
{
    public function __construct(
        public PlayerId   $playerId,
        public CategoryId $categoryId,
    ) {
    }
}
