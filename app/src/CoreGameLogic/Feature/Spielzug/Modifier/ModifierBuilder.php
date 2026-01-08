<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Modifier;

use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ModifierParameters;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

readonly final class ModifierBuilder
{
    /**
     * @param ModifierId $modifierId
     * @param PlayerId|null $playerId
     * @param PlayerTurn $playerTurn
     * @param Year $year
     * @param ModifierParameters $modifierParameters
     * @param string $description
     * @return Modifier[]
     */
    public static function build(
        ModifierId $modifierId,
        PlayerId|null $playerId,
        PlayerTurn $playerTurn,
        Year $year,
        ModifierParameters $modifierParameters,
        string $description
    ): array {
        return match ($modifierId) {
            ModifierId::GEHALT_CHANGE => [
                new GehaltModifier(
                    playerTurn: $playerTurn,
                    description: $description,
                    year: $year,
                    percentage: $modifierParameters->modifyGehaltPercent ?? throw new \RuntimeException("missing parameter"), // TODO better error message
                ),
            ],
            ModifierId::AUSSETZEN => [
                new AussetzenModifier(
                    playerId: $playerId ?? throw new \RuntimeException("missing parameter 'playerId'"),
                    playerTurn: $playerTurn,
                    description: $description,
                    numberOfSkippedTurns: $modifierParameters->numberOfTurns ?? 1,
                ),
            ],
            ModifierId::BIND_ZEITSTEIN_FOR_JOB => [
                new BindZeitsteinForJobModifier(
                    playerId: $playerId ?? throw new \RuntimeException("missing parameter 'playerId'"),
                    playerTurn: $playerTurn,
                    description: $description,
                ),
            ],
            ModifierId::LEBENSHALTUNGSKOSTEN_KIND_INCREASE => [
                new AdditionalLebenshaltungskostenKindModifier(
                    playerTurn: $playerTurn,
                    description: $description,
                    additionalPercentage: floatval($modifierParameters->modifyAdditionalLebenshaltungskostenPercentage ?? throw new \RuntimeException("missing parameter")),
                ),
            ],
            ModifierId::LEBENSHALTUNGSKOSTEN_MIN_VALUE => [
                new LebenshaltungskostenKindMinValueModifier(
                    playerTurn: $playerTurn,
                    description: $description,
                    minValueChange: $modifierParameters->modifyLebenshaltungskostenMinValue ?? throw new \RuntimeException("missing parameter"),
                ),
            ],
            ModifierId::INVESTITIONSSPERRE => [
                new InvestitionssperreModifier(
                    playerTurn: $playerTurn,
                    description: $description,
                ),
            ],
            ModifierId::BERUFSUNFAEHIGKEITSVERSICHERUNG => [
                new BerufsunfaehigkeitJobsperreModifier(
                    playerTurn: $playerTurn,
                    description: $description,
                ),
                new BerufsunfaehigkeitGehaltModifier(
                    playerTurn: $playerTurn,
                    description: $description,
                ),
            ],
            ModifierId::BILDUNG_UND_KARRIERE_COST => [
                new BildungUndKarriereCostModifier(
                    playerTurn: $playerTurn,
                    description: $description,
                    year: $year,
                    percentage: $modifierParameters->modifyKostenBildungUndKarrierePercent ?? throw new \RuntimeException("missing parameter"),
                ),
            ],
            ModifierId::SOZIALES_UND_FREIZEIT_COST => [
                new SozialesUndFreizeitCostModifier(
                    playerTurn: $playerTurn,
                    description: $description,
                    year: $year,
                    percentage: $modifierParameters->modifyKostenSozialesUndFreizeitPercent ?? throw new \RuntimeException("missing parameter"),
                ),
            ],
            ModifierId::LEBENSHALTUNGSKOSTEN_KONJUNKTURPHASE_MULTIPLIER => [
                new LebenshaltungskostenKonjunkturphaseModifier(
                    playerTurn: $playerTurn,
                    description: $description,
                    year: $year,
                    percentage: floatval($modifierParameters->modifyLebenshaltungskostenMultiplier ?? throw new \RuntimeException("missing parameter")),
                ),
            ],
            ModifierId::KREDITSPERRE => [
                new KreditsperreModifier(
                    playerTurn: $playerTurn,
                    description: $description,
                    year: $year,
                ),
            ],
            ModifierId::INCREASED_CHANCE_FOR_REZESSION => [
                new IncreasedChanceForRezessionModifier(
                    playerTurn: $playerTurn,
                    description: $description,
                    year: $year,
                ),
            ],
            ModifierId::FOR_TESTING_ONLY_NEVER_TRIGGER_EREIGNIS => [
                // @phpstan-ignore disallowed.new
                new ForTestingOnlyNeverTriggerEreignisModifier(
                    playerTurn: $playerTurn,
                    description: $description,
                )
            ],
            ModifierId::FOR_TESTING_ONLY_ALWAYS_TRIGGER_EREIGNIS => [
                // @phpstan-ignore disallowed.new
                new ForTestingOnlyAlwaysTriggerEreignisModifier(
                    playerTurn: $playerTurn,
                    description: $description,
                )
            ],
            default => [],
        };
    }
}
