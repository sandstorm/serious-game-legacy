<?php

declare(strict_types=1);

namespace Domain\Definitions\KonjunkturzyklusDefinition\Model;

use Domain\CoreGameLogic\Dto\Enum\KompetenzbereichEnum;

/**
 * represents the model of the Kompetenzbereich used by the repository to fill the game with data
 */
class Kompetenzbereich
{
    public function __construct(
        public KompetenzbereichEnum $name,
        public int $kompetenzsteine = 0,
    ) {
    }
}
