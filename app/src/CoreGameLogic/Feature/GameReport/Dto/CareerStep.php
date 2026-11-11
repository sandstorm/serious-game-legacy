<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\GameReport\Dto;

use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

/**
 * An accepted job, or quitting a job (then the Gehalt is null).
 */
final readonly class CareerStep
{
    public function __construct(
        public Year $year,
        public string $title,
        public ?MoneyAmount $gehalt,
    ) {
    }

    public function isQuit(): bool
    {
        return $this->gehalt === null;
    }
}
