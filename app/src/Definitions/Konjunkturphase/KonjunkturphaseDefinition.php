<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturphase;

use Domain\Definitions\Auswirkung\AuswirkungDefinition;
use Domain\Definitions\Kompetenzbereich\KompetenzbereichDefinition;
use Domain\Definitions\Konjunkturphase\Enum\KonjunkturphaseTypeEnum;

/**
 * represents the model of the konjunkturphase used by the repository to fill the game with data
 */
class KonjunkturphaseDefinition {
    /**
     * @param int $id
     * @param KonjunkturphaseTypeEnum $type
     * @param string $description
     * @param string $additionalEvents
     * @param int $leitzins
     * @param KompetenzbereichDefinition[] $kompetenzbereiche
     * @param AuswirkungDefinition[] $auswirkungen
     */
    public function __construct(
        public int                     $id,
        public KonjunkturphaseTypeEnum $type,
        public string                  $description,
        public string                  $additionalEvents,
        public int                     $leitzins,
        public array                   $kompetenzbereiche,
        public array                   $auswirkungen = [],
    )
    {
    }
}
