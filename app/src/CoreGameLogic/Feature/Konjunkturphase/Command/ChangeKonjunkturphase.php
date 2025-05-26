<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\Pile;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseDefinition;

final readonly class ChangeKonjunkturphase implements CommandInterface
{
    public static function create(): self
    {
        return new self();
    }

    /**
     * @param Pile[] $fixedCardIdOrderingForTesting
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
    public function withFixedCardIdOrderForTesting(Pile ...$piles): self
    {
        return new self($this->fixedKonjunkturphaseForTesting, $piles);
    }

}
