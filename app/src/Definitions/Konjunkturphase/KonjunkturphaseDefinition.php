<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturphase;

use Domain\Definitions\Konjunkturphase\Dto\AuswirkungDefinition;
use Domain\Definitions\Konjunkturphase\Dto\KompetenzbereichDefinition;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphasenId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphaseTypeEnum;

/**
 * represents the model of the konjunkturphase used by the repository to fill the game with data
 */
class KonjunkturphaseDefinition
{
    /**
     * @param KonjunkturphasenId $id
     * @param KonjunkturphaseTypeEnum $type
     * @param string $description
     * @param string $additionalEvents
     * @param float $zinssatz
     * @param KompetenzbereichDefinition[] $kompetenzbereiche
     * @param AuswirkungDefinition[] $auswirkungen
     */
    public function __construct(
        public KonjunkturphasenId      $id,
        public KonjunkturphaseTypeEnum $type,
        public string                  $description,
        public string                  $additionalEvents,
        public float                   $zinssatz,
        public array                   $kompetenzbereiche,
        public array                   $auswirkungen = [],
    )
    {
    }
}
