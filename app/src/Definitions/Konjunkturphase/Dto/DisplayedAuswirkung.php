<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturphase\Dto;

/**
 * An Auswirkung of a Konjunkturphase as it is shown to the players, see
 * {@see \Domain\Definitions\Konjunkturphase\KonjunkturphaseDefinition::getDisplayedAuswirkungen()}
 */
final readonly class DisplayedAuswirkung
{
    /**
     * @param string $label
     * @param float $value
     * @param string $unit e.g. "%" or " €"
     * @param bool $isLowerBetter true, if a lower value is better for the players (e.g. Kreditzins)
     */
    public function __construct(
        public string $label,
        public float $value,
        public string $unit,
        public bool $isLowerBetter,
    ) {
    }
}
