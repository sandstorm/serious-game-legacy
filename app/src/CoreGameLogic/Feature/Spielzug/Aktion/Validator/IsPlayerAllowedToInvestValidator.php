<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\ModifierCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if the player is allowed to invest -> does not have an Investitionssperre-Modifier and is not insolvent
 */
final class IsPlayerAllowedToInvestValidator extends AbstractValidator
{
    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $hasInvestitionssperre = ModifierCalculator::forStream($gameEvents)->forPlayer($playerId)->modify($gameEvents, HookEnum::INVESTITIONSSPERRE, false);
        if ($hasInvestitionssperre) {
            return new AktionValidationResult(
                canExecute: false,
                reason: "Du darfst diese Konjunkturphase keine Investitionen tätigen."
            );
        }

        $isInsolvent = PlayerState::isPlayerInsolvent($gameEvents, $playerId);
        if ($isInsolvent) {
            return new AktionValidationResult(
              canExecute: false,
              reason: "Du bist insolvent."
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
