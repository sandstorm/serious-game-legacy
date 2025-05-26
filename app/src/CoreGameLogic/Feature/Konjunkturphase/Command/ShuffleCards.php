<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;


/**
 * @internal use ChangeKonkunkturphase instead - only for development
 * @deprecated use ChangeKonkunkturphase instead- only for development
 */
final readonly class ShuffleCards implements CommandInterface
{
    public static function create(): self
    {
        return new self();
    }

    private function __construct(
    ) {
    }
}
