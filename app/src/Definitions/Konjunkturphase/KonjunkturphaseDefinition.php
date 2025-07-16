<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturphase;

use Domain\Definitions\Konjunkturphase\Dto\AuswirkungDefinition;
use Domain\Definitions\Konjunkturphase\Dto\KompetenzbereichDefinition;
use Domain\Definitions\Konjunkturphase\Dto\Zeitsteine;
use Domain\Definitions\Konjunkturphase\ValueObject\AuswirkungScopeEnum;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
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
     * @param string $name
     * @param string $description
     * @param string $additionalEvents
     * @param Zeitsteine $zeitsteine
     * @param KompetenzbereichDefinition[] $kompetenzbereiche
     * @param AuswirkungDefinition[] $auswirkungen
     */
    public function __construct(
        public KonjunkturphasenId      $id,
        public KonjunkturphaseTypeEnum $type,
        public string                  $name,
        public string                  $description,
        public string                  $additionalEvents,
        public Zeitsteine              $zeitsteine,
        public array                   $kompetenzbereiche,
        public array                   $auswirkungen = [],
    )
    {
    }

    public function getAuswirkungByScope(AuswirkungScopeEnum $scope): AuswirkungDefinition
    {
        foreach ($this->auswirkungen as $auswirkung) {
            if ($auswirkung->scope === $scope) {
                return $auswirkung;
            }
        }

        throw new \RuntimeException(
            'Auswirkung not found for scope: ' . $scope->value,
            1747148685
        );
    }

    public function getKompetenzbereichByCategory(CategoryId $name): KompetenzbereichDefinition
    {
        foreach ($this->kompetenzbereiche as $kompetenzbereich) {
            if ($kompetenzbereich->name === $name) {
                return $kompetenzbereich;
            }
        }

        throw new \RuntimeException(
            'Kompetenzbereich not found for category: ' . $name->value,
            1747148686
        );
    }
}
