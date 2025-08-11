<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\ModifierCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if the player is allowed to take a job -> does not have a Jobsperre/Berufsunfaehigkeit-Modifier
 */
final class IsPlayerAllowedToTakeAJobValidator extends AbstractValidator
{
    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $isSkipped = ModifierCalculator::forStream($gameEvents)->forPlayer($playerId)->modify($gameEvents, HookEnum::BERUFSUNFAEHIGKEIT_JOBSPERRE, false);
        if ($isSkipped) {
            return new AktionValidationResult(
                canExecute: false,
                reason: "Du darfst diese Konjunkturphase keinen neuen Job mehr annehmen."
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
