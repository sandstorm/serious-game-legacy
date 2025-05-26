<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\ValueObject;

use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Lebensziel\Dto\LebenszielPhaseDefinition;

class LebenszielPhase implements \JsonSerializable
{
    public function __construct(
        public LebenszielPhaseDefinition $definition,
        public int $placedKompetenzsteineBildung,
        public int $placedKompetenzsteineFreizeit,
    ) {
    }

    public static function fromDefinition(LebenszielPhaseDefinition $definition): self
    {
        return new self(
            definition: $definition,
            placedKompetenzsteineBildung: 0,
            placedKompetenzsteineFreizeit: 0,
        );
    }

    public function withChange(ResourceChanges $resourceChanges): self
    {
        return new self(
            definition: $this->definition,
            placedKompetenzsteineBildung: $this->placedKompetenzsteineBildung + $resourceChanges->bildungKompetenzsteinChange,
            placedKompetenzsteineFreizeit: $this->placedKompetenzsteineFreizeit + $resourceChanges->freizeitKompetenzsteinChange,
        );
    }

    /**
     * @param array{definition: mixed, placedKompetenzsteineBildung: int, placedKompetenzsteineFreizeit: int} $values
     * @return self
     */
    public function fromArray(array $values): self
    {
        return new self(
            definition: LebenszielPhaseDefinition::fromArray($values['definition']),
            placedKompetenzsteineBildung: $values['placedKompetenzsteineBildung'],
            placedKompetenzsteineFreizeit: $values['placedKompetenzsteineFreizeit'],
        );
    }

    /**
     * @return array{definition: mixed, placedKompetenzsteineBildung: int, placedKompetenzsteineFreizeit: int}
     */
    public function jsonSerialize(): array
    {
        return [
            'definition' => $this->definition->jsonSerialize(),
            'placedKompetenzsteineBildung' => $this->placedKompetenzsteineBildung,
            'placedKompetenzsteineFreizeit' => $this->placedKompetenzsteineFreizeit,
        ];
    }
}
