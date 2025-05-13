<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\Command;

use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Dto\ValueObject\Szenario;

final readonly class JahrWechseln implements CommandInterface
{
    public function __construct(public string $name, public Szenario $szenario)
    {
    }
}
