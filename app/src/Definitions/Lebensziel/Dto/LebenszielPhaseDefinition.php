<?php

declare(strict_types=1);

namespace Domain\Definitions\Lebensziel\Dto;

use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

readonly class LebenszielPhaseDefinition
{
    public function __construct(
        public LebenszielPhaseId $lebenszielPhaseId,
        public string            $description,
        public MoneyAmount       $investitionen,
        public int               $bildungsKompetenzSlots,
        public int               $freizeitKompetenzSlots,
    )
    {
    }

    /**
     * @param array<mixed> $values
     * @return self
     */
    public static function fromArray(array $values): self
    {
        return new self(
            lebenszielPhaseId: LebenszielPhaseId::from($values['lebenszielPhaseId']),
            description: $values['description'],
            investitionen: MoneyAmount::fromString($values['investitionen']),
            bildungsKompetenzSlots: $values['bildungsKompetenzSlots'],
            freizeitKompetenzSlots: $values['freizeitKompetenzSlots'],
        );
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'lebenszielPhaseId' => $this->lebenszielPhaseId->value,
            'description' => $this->description,
            'investitionen' => $this->investitionen,
            'bildungsKompetenzSlots' => $this->bildungsKompetenzSlots,
            'freizeitKompetenzSlots' => $this->freizeitKompetenzSlots,
        ];
    }
}
