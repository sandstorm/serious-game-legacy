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

    public function withFixedKonjunkturphaseForTesting(?KonjunkturphaseDefinition $konjunkturphase = null): self
    {
        return new self($konjunkturphase);
    }

    private function __construct(public ?KonjunkturphaseDefinition $fixedKonjunkturphaseForTesting = null)
    {
    }
}
