<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\CardOrdering;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseDefinition;

final readonly class ChangeKonjunkturphase implements CommandInterface
{
    public static function create(): self
    {
        return new self();
    }

    /**
     * @param CardOrdering[] $fixedCardIdOrderingForTesting
     */
    private function __construct(
        public ?KonjunkturphaseDefinition $fixedKonjunkturphaseForTesting = null,
        public array $fixedCardIdOrderingForTesting = []
    )
    {
    }


    public function withFixedKonjunkturphaseForTesting(?KonjunkturphaseDefinition $konjunkturphase = null): self
    {
        return new self($konjunkturphase, $this->fixedCardIdOrderingForTesting);
    }

    // TODO: withFixedCardOrderingForTesting
    public function withFixedCardIdOrderForTesting(CardOrdering ...$piles): self
    {
        return new self($this->fixedKonjunkturphaseForTesting, $piles);
    }

}
