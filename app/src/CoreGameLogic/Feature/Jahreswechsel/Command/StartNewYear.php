<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Jahreswechsel\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Dto\ValueObject\CurrentYear;
use Domain\CoreGameLogic\Dto\ValueObject\Leitzins;

final readonly class StartNewYear implements CommandInterface
{
    public function __construct(
        public CurrentYear $newYear,
        public Leitzins    $leitzins
    ) {
    }
}
