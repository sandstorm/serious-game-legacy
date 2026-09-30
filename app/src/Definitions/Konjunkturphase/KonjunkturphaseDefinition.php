<?php

declare(strict_types=1);

namespace Domain\Definitions\Konjunkturphase;

use Domain\Definitions\Card\Dto\ModifierParameters;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Konjunkturphase\Dto\AuswirkungDefinition;
use Domain\Definitions\Konjunkturphase\Dto\ConditionalResourceChange;
use Domain\Definitions\Konjunkturphase\Dto\DisplayedAuswirkung;
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
     * @param string $zeitsteineDescription text shown to the players to explain the amount of Zeitsteine, e.g.
     *                                      "+1 Zeitstein für alle" (the amount itself is defined in $zeitsteine)
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
        public string                  $zeitsteineDescription = '',
    ) {
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

    /**
     * The Auswirkungen shown to the players (e.g. when the Konjunkturphase starts). Other Auswirkungen (e.g. the
     * Kursbonus of Aktien, Crypto and Immobilien) are intentionally hidden from the players.
     *
     * @return DisplayedAuswirkung[]
     */
    public function getDisplayedAuswirkungen(): array
    {
        return [
            new DisplayedAuswirkung(
                label: 'Gehalt',
                value: $this->modifierParameters->modifyGehaltPercent ?? 100, // 100 % = no modification
                unit: '%',
                isLowerBetter: false,
            ),
            new DisplayedAuswirkung(
                label: 'Lebenshaltungskosten',
                value: $this->modifierParameters->modifyLebenshaltungskostenMultiplier ?? 100, // 100 % = no modification
                unit: '%',
                isLowerBetter: true,
            ),
            new DisplayedAuswirkung(
                label: AuswirkungScopeEnum::LOANS_INTEREST_RATE->value,
                value: $this->getAuswirkungByScope(AuswirkungScopeEnum::LOANS_INTEREST_RATE)->value,
                unit: '%',
                isLowerBetter: true,
            ),
            new DisplayedAuswirkung(
                label: AuswirkungScopeEnum::DIVIDEND->value,
                value: $this->getAuswirkungByScope(AuswirkungScopeEnum::DIVIDEND)->value,
                unit: ' €',
                isLowerBetter: false,
            ),
        ];
    }

    /**
     * Texts describing the other Auswirkungen of the Konjunkturphase, which are shown to the players: additional
     * modifiers (e.g. Kreditsperre), the conditional ResourceChanges and the Zeitsteine.
     *
     * @return string[]
     */
    public function getDisplayedAuswirkungDescriptions(): array
    {
        // these modifiers are either already shown in the Auswirkungen or intentionally hidden from the players
        $modifierIdsToHide = [
            ModifierId::GEHALT_CHANGE,
            ModifierId::LEBENSHALTUNGSKOSTEN_KONJUNKTURPHASE_MULTIPLIER,
            ModifierId::BILDUNG_UND_KARRIERE_COST,
            ModifierId::SOZIALES_UND_FREIZEIT_COST,
        ];
        $descriptions = [];
        foreach ($this->modifierIds as $modifierId) {
            if (!in_array($modifierId, $modifierIdsToHide, true)) {
                $descriptions[] = $modifierId->value;
            }
        }
        foreach ($this->conditionalResourceChanges as $conditionalResourceChange) {
            $descriptions[] = $conditionalResourceChange->description;
        }
        $descriptions[] = $this->zeitsteineDescription;

        return array_values(array_filter($descriptions, fn (string $description) => $description !== ''));
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
