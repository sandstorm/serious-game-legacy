<?php

declare(strict_types=1);

namespace Domain\Definitions\Lebensziel\Model;

readonly class LebenszielPhaseDefinition
{
    public function __construct(
        public int $bildungsKompetenzSlots,
        public int $freizeitKompetenzSlots,
    ) {
    }

    /**
     * @param array{bildungsKompetenzSlots: mixed, freizeitKompetenzSlots: mixed} $values
     * @return self
     */
    public static function fromArray(array $values): self
    {
        return new self(
            bildungsKompetenzSlots: $values['bildungsKompetenzSlots'],
            freizeitKompetenzSlots: $values['freizeitKompetenzSlots'],
        );
    }

    /**
     * @return array{bildungsKompetenzSlots: mixed, freizeitKompetenzSlots: mixed}
     */
    public function jsonSerialize(): array
    {
        return [
            'bildungsKompetenzSlots' => $this->bildungsKompetenzSlots,
            'freizeitKompetenzSlots' => $this->freizeitKompetenzSlots,
        ];
    }
}
