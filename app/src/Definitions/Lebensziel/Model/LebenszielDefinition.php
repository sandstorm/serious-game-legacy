<?php

declare(strict_types=1);

namespace Domain\Definitions\Lebensziel\Model;

use Domain\CoreGameLogic\Dto\ValueObject\LebenszielId;

readonly class LebenszielDefinition implements \JsonSerializable
{
    // TODO phases, goals, etc
    /**
     * @param LebenszielId $id
     * @param LebenszielPhaseDefinition[] $phases
     */
    public function __construct(
        public LebenszielId $id,
        public array $phases,
    ) {
    }

    public function withUpdatedPhase(int $index, LebenszielPhaseDefinition $phase): self
    {
        $phases = $this->phases;
        $phases[$index] = $phase;
        return new self(
            id: $this->id,
            phases: $phases,
        );
    }

    /**
     * @param array{value: string, phases: array<string, mixed>} $values
     * @return self
     */
    public static function fromArray(array $values): self
    {
        $phases = [];
        foreach ($values['phases'] as $phase) {
            $phases[] = LebenszielPhaseDefinition::fromArray($phase);
        }
        return new self(
            id: new LebenszielId($values['value']),
            phases: $phases,
        );
    }

    public function __toString(): string
    {
        return '[Lebensziel: '.$this->id->value.']';
    }

    /**
     * @return array{value: string, phases: array<string, mixed>}
     */
    public function jsonSerialize(): array
    {
        return [
            'value' => $this->id->jsonSerialize(),
            'phases' => $this->phases
        ];
    }
}
