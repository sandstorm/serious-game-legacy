<?php

declare(strict_types=1);

namespace Domain\Definitions\Lebensziel\Dto;


use Domain\Definitions\Lebensziel\ValueObject\LebenszielId;

readonly class LebenszielDefinition implements \JsonSerializable
{
    /**
     * @param LebenszielPhaseDefinition[] $phaseDefinitions
     */
    public function __construct(
        public LebenszielId $id,
        public string $name,
        public array $phaseDefinitions,
    ) {
    }

    /**
     * @param array<mixed> $values
     * @return self
     */
    public static function fromArray(array $values): self
    {
        $phases = [];
        foreach ($values['phases'] as $phase) {
            $phases[] = LebenszielPhaseDefinition::fromArray($phase);
        }
        return new self(
            id: LebenszielId::create($values['id']),
            name: $values['name'],
            phaseDefinitions: $phases,
        );
    }

    public function __toString(): string
    {
        return '[Lebensziel: '.$this->name.']';
    }

    /**
     * @return array<mixed>
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
