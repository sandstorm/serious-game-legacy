<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\Pile;

final readonly class ShuffleCards implements CommandInterface
{
    public static function create(): self
    {
        return new self();
    }

    /**
     * @param Pile[] $fixedCardIdOrderingForTesting
     */
    private function __construct(
        public array $fixedCardIdOrderingForTesting = [],
    ) {
        foreach ($this->fixedCardIdOrderingForTesting as $pile) {
            assert($pile instanceof Pile);
        }
    }

    public function withFixedCardIdOrderForTesting(Pile ...$piles): self
    {
        return new self($piles);
    }
}
