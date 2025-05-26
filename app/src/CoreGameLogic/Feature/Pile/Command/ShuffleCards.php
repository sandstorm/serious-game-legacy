<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Pile\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Feature\Pile\State\dto\Pile;
use Domain\Definitions\Cards\ValueObject\PileId;

final readonly class ShuffleCards implements CommandInterface
{
    /**
     * @param PileId|null $pileId the pile id to shuffle. If null, all piles will be shuffled.
     * @return self
     */
    public static function create(?PileId $pileId = null): self
    {
        return new self($pileId);
    }

    /**
     * @param Pile[] $fixedCardIdOrderingForTesting
     */
    private function __construct(
        public ?PileId $pileId = null,
        public array $fixedCardIdOrderingForTesting = [],
    ) {
        foreach ($this->fixedCardIdOrderingForTesting as $pile) {
            assert($pile instanceof Pile);
        }
    }

    public function withFixedCardIdOrderForTesting(Pile ...$piles): self
    {
        return new self(null, $piles);
    }
}
