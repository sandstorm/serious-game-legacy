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
    public static function build(
        ModifierId $modifierId,
        PlayerId $playerId,
        PlayerTurn $playerTurn,
        Year $year,
        ModifierParameters $modifierParameters,
        string $description
    ): Modifier {
        return match ($modifierId) {
            ModifierId::GEHALT_CHANGE => new GehaltModifier(
                playerId: $playerId,
                playerTurn: $playerTurn,
                description: $description,
                activeYear: $year,
                percentage: $modifierParameters->modifyGehaltPercent ?? throw new \RuntimeException("missing parameter"),// TODO better error message
            ),
            ModifierId::AUSSETZEN => new AussetzenModifier(
                playerId: $playerId,
                playerTurn: $playerTurn,
                description: $description,
                numberOfSkippedTurns: $modifierParameters->numberOfTurns ?? throw new \RuntimeException("missing parameter"),
            ),
            ModifierId::BIND_ZEITSTEIN_FOR_JOB => new BindZeitsteinForJobModifier(
                playerId: $playerId,
                playerTurn: $playerTurn,
                description: $description,
            ),
        };
    }

}
