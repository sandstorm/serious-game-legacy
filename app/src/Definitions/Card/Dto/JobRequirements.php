<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

final readonly class JobRequirements
{
    public function __construct(
        public int $zeitsteine = 0,
        public int $bildungKompetenzsteine = 0,
        public int $freizeitKompetenzsteine = 0,
    )
    {
    }

    /**
     * @param array{zeitsteine: int, bildungKompetenzsteine: int, freizeitKompetenzsteine: int} $values
     * @return self
     */
    public static function fromArray(array $values): self
    {
        return new self(
            zeitsteine: $values['zeitsteine'],
            bildungKompetenzsteine: $values['bildungKompetenzsteine'],
            freizeitKompetenzsteine: $values['freizeitKompetenzsteine'],
        );
    }
}
