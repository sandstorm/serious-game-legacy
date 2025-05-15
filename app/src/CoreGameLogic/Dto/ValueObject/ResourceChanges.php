<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

readonly class ResourceChanges implements \JsonSerializable
{
    public function __construct(
        public int $guthabenChange = 0,
        public int $zeitsteineChange = 0,
        public int $bildungKompetenzsteinChange = 0,
        public int $freizeitKompetenzsteinChange = 0,
    )
    {
    }

    /**
     * @param array<string,mixed> $values
     */
    public static function fromArray(array $values): self
    {
        return new self(
            guthabenChange: $values['guthabenChange'],
            zeitsteineChange:  $values['zeitsteineChange'],
            bildungKompetenzsteinChange: $values['bildungKompetenzsteinChange'],
            freizeitKompetenzsteinChange: $values['freizeitKompetenzsteinChange'],
        );
    }

    public function __toString(): string
    {
        return '[guthabenChange: '.$this->guthabenChange.' zeitsteineChange: '.$this->zeitsteineChange.']';
    }

    public function accumulate(self $change): self
    {
        return new self(
            guthabenChange: $this->guthabenChange + $change->guthabenChange,
            zeitsteineChange: $this->zeitsteineChange + $change->zeitsteineChange,
            bildungKompetenzsteinChange: $this->bildungKompetenzsteinChange + $change->bildungKompetenzsteinChange,
            freizeitKompetenzsteinChange: $this->freizeitKompetenzsteinChange + $change->freizeitKompetenzsteinChange,
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'guthabenChange' => $this->guthabenChange,
            'zeitsteineChange' => $this->zeitsteineChange,
            'bildungKompetenzsteinChange' => $this->bildungKompetenzsteinChange,
            'freizeitKompetenzsteinChange' => $this->freizeitKompetenzsteinChange,
        ];
    }
}
