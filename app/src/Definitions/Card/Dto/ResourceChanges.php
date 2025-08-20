<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

use Domain\Definitions\Card\ValueObject\MoneyAmount;
use JsonSerializable;

final readonly class ResourceChanges implements JsonSerializable
{
    public function __construct(
        public MoneyAmount $guthabenChange = new MoneyAmount(0),
        public int $zeitsteineChange = 0,
        public float $bildungKompetenzsteinChange = 0,
        public int $freizeitKompetenzsteinChange = 0,
    )
    {
    }

    /**
     * @param array{
     *     guthabenChange: float,
     *     zeitsteineChange: int,
     *     bildungKompetenzsteinChange: float,
     *     freizeitKompetenzsteinChange: int,
     * } $values
     */
    public static function fromArray(array $values): self
    {
        return new self(
            guthabenChange: new MoneyAmount($values['guthabenChange']),
            zeitsteineChange: $values['zeitsteineChange'],
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
            guthabenChange: $this->guthabenChange->add($change->guthabenChange),
            zeitsteineChange: $this->zeitsteineChange + $change->zeitsteineChange,
            bildungKompetenzsteinChange: $this->bildungKompetenzsteinChange + $change->bildungKompetenzsteinChange,
            freizeitKompetenzsteinChange: $this->freizeitKompetenzsteinChange + $change->freizeitKompetenzsteinChange,
        );
    }

    /**
     * @return array{
     *      guthabenChange: float,
     *      zeitsteineChange: int,
     *      bildungKompetenzsteinChange: float,
     *      freizeitKompetenzsteinChange: int,
     *  }
     */
    public function jsonSerialize(): array
    {
        return [
            // TODO discuss, without jsonSerialize() MoneyAmount is an object in this array
            'guthabenChange' => $this->guthabenChange->jsonSerialize(),
            'zeitsteineChange' => $this->zeitsteineChange,
            'bildungKompetenzsteinChange' => $this->bildungKompetenzsteinChange,
            'freizeitKompetenzsteinChange' => $this->freizeitKompetenzsteinChange,
        ];
    }
}
