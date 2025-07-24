<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

use Domain\Definitions\Konjunkturphase\ValueObject\Year;

/**
 * Use this interface for cards that may require only be added to the pile after a certain year.
 */
interface CardWithYear
{
    public function getYear(): Year;
}
