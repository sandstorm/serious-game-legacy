<?php

declare(strict_types=1);

namespace Domain\Definitions\Lebensziel\Dto;

readonly class LebenszielPhaseDefinition
{
    public function __construct(
        public int    $phase,
        public string $description,
        public float  $investitionen,
        public float  $erwerbseinkommen,
        public int    $bildungsKompetenzSlots,
        public int    $freizeitKompetenzSlots,
    ) {
    }

    /**
     * @param array $values
     * @return self
     */
    public static function fromArray(array $values): self
    {
        return new self(
            phase: $values['phase'],
            description: $values['description'],
            investitionen: $values['investitionen'],
            erwerbseinkommen: $values['erwerbseinkommen'],
            bildungsKompetenzSlots: $values['bildungsKompetenzSlots'],
            freizeitKompetenzSlots: $values['freizeitKompetenzSlots'],
        );
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'phase' => $this->phase,
            'description' => $this->description,
            'investitionen' => $this->investitionen,
            'erwerbseinkommen' => $this->erwerbseinkommen,
            'bildungsKompetenzSlots' => $this->bildungsKompetenzSlots,
            'freizeitKompetenzSlots' => $this->freizeitKompetenzSlots,
        ];
    }
}
