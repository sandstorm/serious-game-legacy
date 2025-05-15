<?php

declare(strict_types=1);

namespace Domain\Definitions\Lebensziel;


readonly class LebenszielDefinition implements \JsonSerializable
{
    /**
     * @param int $id
     * @param string $name
     * @param LebenszielPhaseDefinition[] $phaseDefinitions
     */
    public function __construct(
        public int $id,
        public string $name,
        public array $phaseDefinitions,
    ) {
    }

    /**
     * @param array{id: int, name: string, phases: array<string, mixed>} $values
     * @return self
     */
    public static function fromArray(array $values): self
    {
        $phases = [];
        foreach ($values['phases'] as $phase) {
            $phases[] = LebenszielPhaseDefinition::fromArray($phase);
        }
        return new self(
            id: $values['id'],
            name: $values['name'],
            phaseDefinitions: $phases,
        );
    }

    public function __toString(): string
    {
        return '[Lebensziel: '.$this->name.']';
    }

    /**
     * @return array{id: int, name: string, phases: array<LebenszielPhaseDefinition>}
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phases' => $this->phaseDefinitions
        ];
    }
}
