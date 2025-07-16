<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\ModifierCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if the player does not skip a turn
 */
final class DoesNotSkipTurnValidator extends AbstractValidator
{
    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $isSkipped = ModifierCalculator::forStream($gameEvents)->forPlayer($playerId)->modify($gameEvents, HookEnum::AUSSETZEN, false);
        if ($isSkipped) {
            return new AktionValidationResult(
                canExecute: false,
                reason: "Du setzt diese Runde aus."
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
