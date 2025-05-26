<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;

final readonly class StartGame implements CommandInterface
{
    public static function create(): self
    {
        return new self();
    }

    private function __construct()
    {
    }
}
