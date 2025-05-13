<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\KonjunkturzyklusWechseln\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Dto\ValueObject\Konjunkturzyklus;

final readonly class KonjunkturzyklusWechseln implements CommandInterface
{
    public function __construct(public Konjunkturzyklus $konjunkturzyklus)
    {
    }
}
