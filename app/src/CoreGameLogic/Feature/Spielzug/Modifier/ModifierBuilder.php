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
     * @param PlayerId $playerId
     * @param PlayerTurn $playerTurn
     * @param Year $year
     * @param ModifierParameters $modifierParameters
     * @param string $description
     * @return Modifier[]
     */
    public static function build(
        ModifierId $modifierId,
        PlayerId $playerId,
        PlayerTurn $playerTurn,
        Year $year,
        ModifierParameters $modifierParameters,
        string $description
    ): array {
        return match ($modifierId) {
            ModifierId::GEHALT_CHANGE => [new GehaltModifier(
                playerId: $playerId,
                playerTurn: $playerTurn,
                description: $description,
                activeYear: $year,
                percentage: $modifierParameters->modifyGehaltPercent ?? throw new \RuntimeException("missing parameter"),// TODO better error message
            )],
            ModifierId::AUSSETZEN => [new AussetzenModifier(
                playerId: $playerId,
                playerTurn: $playerTurn,
                description: $description,
                numberOfSkippedTurns: $modifierParameters->numberOfTurns ?? 1,
            )],
            ModifierId::BIND_ZEITSTEIN_FOR_JOB => [new BindZeitsteinForJobModifier(
                playerId: $playerId,
                playerTurn: $playerTurn,
                description: $description,
            )],
            ModifierId::LEBENSHALTUNGSKOSTEN_MULTIPLIER => [new LebenshaltungskostenMultiplierModifier(
                playerId: $playerId,
                playerTurn: $playerTurn,
                description: $description,
                activeYear: $year,
                multiplier: $modifierParameters->modifyLebenshaltungskostenMultiplier ?? throw new \RuntimeException("missing parameter"),
            )],
            ModifierId::LEBENSHALTUNGSKOSTEN_MIN_VALUE => [new LebenshaltungskostenMinValueModifier(
                playerId: $playerId,
                playerTurn: $playerTurn,
                description: $description,
                activeYear: $year,
                minValueChange: $modifierParameters->modifyLebenshaltungskostenMinValue ?? throw new \RuntimeException("missing parameter"),
            )],
            ModifierId::INVESTITIONSSPERRE => [new InvestitionssperreModifier(
                playerId: $playerId,
                playerTurn: $playerTurn,
                description: $description,
            )],
            ModifierId::BERUFSUNFAEHIGKEITSVERSICHERUNG => [
                new BerufsunfaehigkeitJobsperreModifier(
                    playerId: $playerId,
                    playerTurn: $playerTurn,
                    description: $description,
                ),
                new BerufsunfaehigkeitGehaltModifier(
                    playerId: $playerId,
                    playerTurn: $playerTurn,
                    description: $description,
                ),
            ],
            default => [],
        };
    }

}
