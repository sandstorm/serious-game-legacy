<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturzyklus;

use Domain\Definitions\Kompetenzbereich\KompetenzbereichDefinition;
use Domain\Definitions\Konjunkturzyklus\Enum\KonjunkturzyklusTypeEnum;

/**
 * represents the model of the konjunkturzyklus used by the repository to fill the game with data
 */
class
KonjunkturzyklusDefinition
{
    /**
     * @param int $id
     * @param KonjunkturzyklusTypeEnum $type
     * @param string $description
     * @param int $leitzins
     * @param KompetenzbereichDefinition[] $kompetenzbereiche
     */
    public function __construct(
        public int                      $id,
        public KonjunkturzyklusTypeEnum $type,
        public string                   $description,
        public int                      $leitzins,
        public array                    $kompetenzbereiche,
    )
    {
    }
}
