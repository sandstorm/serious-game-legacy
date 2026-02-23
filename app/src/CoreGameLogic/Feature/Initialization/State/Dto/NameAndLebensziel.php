<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\State\Dto;

use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Lebensziel\Dto\LebenszielDefinition;

final readonly class NameAndLebensziel
{
    public function __construct(
        public PlayerId $playerId,
        public ?string $name,
        public ?LebenszielDefinition $lebensziel
    ) {
    }

    public function hasNameAndLebensziel(): bool
    {
        return $this->name !== null && $this->lebensziel !== null;
    }
}
