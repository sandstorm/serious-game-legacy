<?php

declare(strict_types=1);

namespace Domain\Definitions\KonjunkturzyklusDefinition\Model;

use Domain\CoreGameLogic\Dto\Enum\KonjunkturzyklusTypeEnum;

/**
 * represents the model of the konjunkturzyklus used by the repository to fill the game with data
 */
class Konjunkturzyklus
{
    /**
     * @param int $id
     * @param KonjunkturzyklusTypeEnum $type
     * @param string $description
     * @param int $leitzins
     * @param Kompetenzbereich[] $kompetenzbereiche
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
