<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\ModifierCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if the player is allowed to invest -> does not have an Investitionssperre-Modifier
 */
final class IsPlayerAllowedToInvestValidator extends AbstractValidator
{
    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $isSkipped = ModifierCalculator::forStream($gameEvents)->forPlayer($playerId)->modify($gameEvents, HookEnum::INVESTITIONSSPERRE, false);
        if ($isSkipped) {
            return new AktionValidationResult(
                canExecute: false,
                reason: "Du darfst diese Konjunkturphase keine Investitionen tätigen."
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
