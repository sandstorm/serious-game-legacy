<?php

declare(strict_types=1);

namespace Domain\Definitions\Lebensziel\Model;

readonly class LebenszielDefinition implements \JsonSerializable
{
    // TODO phases, goals, etc
    /**
     * @param string $value
     * @param LebenszielPhaseDefinition[] $phases
     */
    public function __construct(
        public string $value,
        public array $phases,
    ) {
    }

    public static function fromArray(array $values): self
    {
        $phases = [];
        foreach ($values['phases'] as $phase) {
            $phases[] = LebenszielPhaseDefinition::fromArray($phase);
        }
        return new self(
            value: $values['value'],
            phases: $phases,
        );
    }

    public function __toString(): string
    {
        return '[Lebensziel: '.$this->value.']';
    }

    public function jsonSerialize(): array
    {
        return [
            'value' => $this->value,
            'phases' => $this->phases
        ];
    }
}
