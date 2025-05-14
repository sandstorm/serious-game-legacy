<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\KonjunkturzyklusWechseln\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\Definitions\Konjunkturzyklus\KonjunkturzyklusDefinition;

final readonly class KonjunkturzyklusWechseln implements CommandInterface
{
    public static function create(): self
    {
        return new self();
    }

    public static function createWithFixedKonjunkturzyklusForTesting(?KonjunkturzyklusDefinition $konjunkturzyklus = null): self
    {
        return new self($konjunkturzyklus);
    }

    private function __construct(public ?KonjunkturzyklusDefinition $fixedKonjunkturzyklusForTesting = null)
    {
    }
}
