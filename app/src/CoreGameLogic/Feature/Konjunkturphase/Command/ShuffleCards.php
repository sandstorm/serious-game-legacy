<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\CardOrdering;


/**
 * @internal use ChangeKonkunkturphase instead
 * @deprecated use ChangeKonkunkturphase instead
 */
final readonly class ShuffleCards implements CommandInterface
{
    public static function create(): self
    {
        return new self();
    }

    /**
     * @param CardOrdering[] $fixedCardIdOrderingForTesting
     */
    private function __construct(
        public array $fixedCardIdOrderingForTesting = [],
    ) {
    }
}
