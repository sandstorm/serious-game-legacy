<?php

declare(strict_types=1);

namespace Domain\Definitions\Lebensziel;

readonly class LebenszielPhaseDefinition
{
    public function __construct(
        public int $phase,
        public string $description,
        public float $invenstition,
        public float $erwerbseinkommen,
        public int $bildungsKompetenzSlots,
        public int $freizeitKompetenzSlots,
    ) {
    }

    /**
     * @param array<string, mixed> $values
     * @return self
     */
    public static function fromArray(array $values): self
    {
        return new self(
            phase: $values['phase'],
            description: $values['description'],
            invenstition: $values['invenstition'],
            erwerbseinkommen: $values['erwerbseinkommen'],
            bildungsKompetenzSlots: $values['bildungsKompetenzSlots'],
            freizeitKompetenzSlots: $values['freizeitKompetenzSlots'],
        );
    }

    /**
     * @return array{phase: int, description: string, invenstition: float, erwerbseinkommen: float, bildungsKompetenzSlots: int, freizeitKompetenzSlots: int}
     */
    public function jsonSerialize(): array
    {
        return [
            'phase' => $this->phase,
            'description' => $this->description,
            'invenstition' => $this->invenstition,
            'erwerbseinkommen' => $this->erwerbseinkommen,
            'bildungsKompetenzSlots' => $this->bildungsKompetenzSlots,
            'freizeitKompetenzSlots' => $this->freizeitKompetenzSlots,
        ];
    }
}
