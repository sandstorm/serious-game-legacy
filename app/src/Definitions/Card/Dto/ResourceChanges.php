<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

final readonly class ResourceChanges implements \JsonSerializable
{
    public function __construct(
        public float $guthabenChange = 0,
        public int $zeitsteineChange = 0,
        public int $bildungKompetenzsteinChange = 0,
        public int $freizeitKompetenzsteinChange = 0,
        public float $newErwerbseinkommen = 0, // TODO not yet sure if this should be somewhere else
        public int $erwerbseinkommenChangeInPercent = 0,
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
            guthabenChange: $values['guthabenChange'],
            zeitsteineChange:  $values['zeitsteineChange'],
            bildungKompetenzsteinChange: $values['bildungKompetenzsteinChange'],
            freizeitKompetenzsteinChange: $values['freizeitKompetenzsteinChange'],
            newErwerbseinkommen: $values['newErwerbseinkommen'],
            erwerbseinkommenChangeInPercent: $values['erwerbseinkommenChangeInPercent'],
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
            newErwerbseinkommen: $change->newErwerbseinkommen, // TODO this should not accumulate, since the old job is replaced
            erwerbseinkommenChangeInPercent: $this->erwerbseinkommenChangeInPercent + $change->erwerbseinkommenChangeInPercent,
        );
    }

    public function jsonSerialize(): mixed
    {
        return [
            'guthabenChange' => $this->guthabenChange,
            'zeitsteineChange' => $this->zeitsteineChange,
            'bildungKompetenzsteinChange' => $this->bildungKompetenzsteinChange,
            'freizeitKompetenzsteinChange' => $this->freizeitKompetenzsteinChange,
            'newErwerbseinkommen' => $this->newErwerbseinkommen,
            'erwerbseinkommenChangeInPercent' => $this->erwerbseinkommenChangeInPercent,
        ];
    }
}
