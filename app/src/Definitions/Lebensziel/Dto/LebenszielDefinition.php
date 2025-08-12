<?php

declare(strict_types=1);

namespace Domain\Definitions\Lebensziel\Dto;

use Domain\Definitions\Lebensziel\ValueObject\LebenszielId;
use JsonSerializable;

readonly class LebenszielDefinition implements JsonSerializable
{
    /**
     * @param LebenszielPhaseDefinition[] $phaseDefinitions
     */
    public function __construct(
        public LebenszielId $id,
        public string $name,
        public string $description,
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
            description: $values['description'],
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
            'description' => $this->description,
            'phases' => $this->phaseDefinitions
        ];
    }
}
