<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

readonly class ResourceChanges implements \JsonSerializable
{
    public function __construct(
        public int $guthabenChange = 0,
        public int $bildungKompetenzsteinChange = 0,
        public int $freizeitKompetenzsteinChange = 0,
    ) {
    }

    /**
     * @param array<string,mixed> $values
     */
    public static function fromArray(array $values): self
    {
        return new self(
            guthabenChange: $values['guthabenChange'],
            bildungKompetenzsteinChange: $values['bildungKompetenzsteinChange'],
            freizeitKompetenzsteinChange: $values['freizeitKompetenzsteinChange'],
        );
    }

    public function __toString(): string
    {
        return '[guthabenChange: '.$this->guthabenChange.']';
    }

    public function accumulate(self $change): self
    {
        return new self(
            guthabenChange: $this->guthabenChange + $change->guthabenChange,
            bildungKompetenzsteinChange: $this->bildungKompetenzsteinChange + $change->bildungKompetenzsteinChange,
            freizeitKompetenzsteinChange: $this->freizeitKompetenzsteinChange + $change->freizeitKompetenzsteinChange,
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'guthabenChange' => $this->guthabenChange,
            'bildungKompetenzsteinChange' => $this->bildungKompetenzsteinChange,
            'freizeitKompetenzsteinChange' => $this->freizeitKompetenzsteinChange,
        ];
    }
}
