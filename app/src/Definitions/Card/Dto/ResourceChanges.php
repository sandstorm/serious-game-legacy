<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

use Domain\Definitions\Card\ValueObject\MoneyAmount;

final readonly class ResourceChanges implements \JsonSerializable
{
    public function __construct(
        public MoneyAmount $guthabenChange = new MoneyAmount(0),
        public int $zeitsteineChange = 0,
        public int $bildungKompetenzsteinChange = 0,
        public int $freizeitKompetenzsteinChange = 0,
    )
    {
    }

    /**
     * @param array{
     *     guthabenChange: int,
     *     zeitsteineChange: int,
     *     bildungKompetenzsteinChange: int,
     *     freizeitKompetenzsteinChange: int,
     *     newErwerbseinkommen: int,
     *     erwerbseinkommenChangeInPercent: int,
     * } $values
     */
    public static function fromArray(array $values): self
    {
        return new self(
            guthabenChange: new MoneyAmount($values['guthabenChange']),
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
            guthabenChange: $this->guthabenChange->add($change->guthabenChange),
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
