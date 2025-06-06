<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\ValueObject;

use Domain\Definitions\Lebensziel\Dto\LebenszielDefinition;

readonly class Lebensziel
{
    /**
     * @param LebenszielDefinition $definition
     * @param LebenszielPhase[] $phases
     */
    public function __construct(
        public LebenszielDefinition $definition,
        public array $phases,
    ) {
    }

    public function withUpdatedPhase(int $index, LebenszielPhase $phase): self
    {
        $phases = $this->phases;
        $phases[$index] = $phase;
        return new self(
            definition: $this->definition,
            phases: $phases,
        );
    }
}
