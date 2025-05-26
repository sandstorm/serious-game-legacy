<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\CardOrder;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseDefinition;

final readonly class ChangeKonjunkturphase implements CommandInterface
{
    public static function create(): self
    {
        return new self();
    }

    /**
     * @param CardOrder[] $fixedCardOrderForTesting
     */
    private function __construct(
        public ?KonjunkturphaseDefinition $fixedKonjunkturphaseForTesting = null,
        public array $fixedCardOrderForTesting = []
    )
    {
    }


    public function withFixedKonjunkturphaseForTesting(?KonjunkturphaseDefinition $konjunkturphase = null): self
    {
        return new self($konjunkturphase, $this->fixedCardOrderForTesting);
    }

    public function withFixedCardOrderForTesting(CardOrder ...$cardOrder): self
    {
        return new self($this->fixedKonjunkturphaseForTesting, $cardOrder);
    }

}
