<?php

declare(strict_types=1);

namespace Domain\Definitions\Lebensziel\Model;

use Domain\CoreGameLogic\Dto\ValueObject\ResourceChanges;

class LebenszielPhaseDefinition
{
    public function __construct(
        public LebenszielKompetenzbereichDefinition $bildungsKompetenz,
        public LebenszielKompetenzbereichDefinition $freizeitKompetenz,
    ) {
    }

    /**
     * @param array{bildungsKompetenz: mixed, freizeitKompetenz: mixed} $values
     * @return self
     */
    public static function fromArray(array $values): self
    {
        return new self(
            bildungsKompetenz: LebenszielKompetenzbereichDefinition::fromArray($values['bildungsKompetenz']),
            freizeitKompetenz: LebenszielKompetenzbereichDefinition::fromArray($values['freizeitKompetenz']),
        );
    }

    /**
     * @return array{bildungsKompetenz: mixed, freizeitKompetenz: mixed}
     */
    public function jsonSerialize(): array
    {
        return [
            'bildungsKompetenz' => $this->bildungsKompetenz->jsonSerialize(),
            'freizeitKompetenz' => $this->freizeitKompetenz->jsonSerialize(),
        ];
    }

    public function withChange(ResourceChanges $resourceChanges): self
    {
        return new self(
            bildungsKompetenz: $this->bildungsKompetenz->accumulate($resourceChanges->bildungKompetenzsteinChange),
            freizeitKompetenz: $this->freizeitKompetenz->accumulate($resourceChanges->freizeitKompetenzsteinChange),
        );
    }
}
