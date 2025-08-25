<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturphase;

use Domain\Definitions\Card\Dto\ModifierParameters;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Konjunkturphase\Dto\AuswirkungDefinition;
use Domain\Definitions\Konjunkturphase\Dto\ConditionalResourceChange;
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
     * @param ModifierId[] $modifierIds
     * @param ModifierParameters $modifierParameters
     * @param AuswirkungDefinition[] $auswirkungen
     * @param ConditionalResourceChange[] $conditionalResourceChanges
     */
    public function __construct(
        public KonjunkturphasenId      $id,
        public KonjunkturphaseTypeEnum $type,
        public string                  $name,
        public string                  $description,
        public string                  $additionalEvents,
        public Zeitsteine              $zeitsteine,
        public array                   $kompetenzbereiche,
        public array                   $modifierIds,
        public ModifierParameters      $modifierParameters,
        public array                   $auswirkungen = [],
        protected array                $conditionalResourceChanges = [],
    )
    {
    }

    /**
     * @return ConditionalResourceChange[]
     */
    public function getConditionalResourceChanges(): array
    {
        return $this->conditionalResourceChanges;
    }

    public function getAuswirkungByScope(AuswirkungScopeEnum $scope): AuswirkungDefinition
    {
        foreach ($this->auswirkungen as $auswirkung) {
            if ($auswirkung->scope === $scope) {
                return $auswirkung;
            }
        }

        // if none found, return a default AuswirkungDefinition with modifier 0.0
        return new AuswirkungDefinition(
            scope: $scope,
            value: 0.0
        );
    }

    public function getDividend(): MoneyAmount
    {
        $auswirkung = $this->getAuswirkungByScope(AuswirkungScopeEnum::DIVIDEND);
        return new MoneyAmount($auswirkung->value);
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

    /**
     * @return ModifierId[]
     */
    public function getModifierIds(): array
    {
        return $this->modifierIds;
    }

    public function getModifierParameters(): ModifierParameters
    {
        return $this->modifierParameters;
    }
}
