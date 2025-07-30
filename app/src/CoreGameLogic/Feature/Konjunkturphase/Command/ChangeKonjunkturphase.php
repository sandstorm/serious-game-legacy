<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseDefinition;

final readonly class ChangeKonjunkturphase implements CommandInterface
{
    public static function create(): self
    {
        return new self();
    }

    /**
     * @param KonjunkturphaseDefinition|null $fixedKonjunkturphaseForTesting
     * @param bool $hasFixedCardOrderForTesting
     */
    private function __construct(
        public ?KonjunkturphaseDefinition $fixedKonjunkturphaseForTesting = null,
        public bool $hasFixedCardOrderForTesting = false
    )
    {
    }


    public function withFixedKonjunkturphaseForTesting(?KonjunkturphaseDefinition $konjunkturphase = null): self
    {
        return new self($konjunkturphase, $this->hasFixedCardOrderForTesting);
    }

    public function withFixedCardOrderForTesting(): self
    {
        return new self($this->fixedKonjunkturphaseForTesting, hasFixedCardOrderForTesting: true);
    }

}
