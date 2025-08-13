<?php

declare(strict_types=1);

namespace Domain\Definitions\Investments\Dto;

use Domain\Definitions\Investments\ValueObject\InvestmentId;

class InvestmentDefinition
{
    /**
     */
    public function __construct(
        public InvestmentId $id,
        public string $description,
        public float $longTermTrend, // in percent
        public float $fluctuations, // in percent
        public float $jumpPerYear,
        public float $jumpSize, // in percent
        public float $jumpControl,
    ) {
    }

    /**
     * @param array<mixed> $values
     * @return self
     */
    public static function fromArray(array $values): self
    {
        return new self(
            id: InvestmentId::from($values['id']),
            description: $values['description'],
            longTermTrend: (float) $values['longTermTrend'],
            fluctuations: (float) $values['fluctuations'],
            jumpPerYear: (float) $values['jumpPerYear'],
            jumpSize: (float) $values['jumpSize'],
            jumpControl: (float) $values['jumpControl'],
        );
    }

    public function __toString(): string
    {
        return '[Investment: '.$this->id->value.']';
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'longTermTrend' => $this->longTermTrend,
            'fluctuations' => $this->fluctuations,
            'jumpPerYear' => $this->jumpPerYear,
            'jumpSize' => $this->jumpSize,
            'jumpControl' => $this->jumpControl,
        ];
    }
}
