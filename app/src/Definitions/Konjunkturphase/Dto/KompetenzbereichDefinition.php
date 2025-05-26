<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturphase\Dto;

use Domain\Definitions\Konjunkturphase\ValueObject\KompetenzbereichEnum;

/**
 * represents the model of the Kompetenzbereich used by the repository to fill the game with data
 */
class KompetenzbereichDefinition
{
    public function __construct(
        public KompetenzbereichEnum $name,
        public int                  $kompetenzsteine = 0,
    )
    {
    }
}
