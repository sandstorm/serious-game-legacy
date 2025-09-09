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
 * Succeeds if the player is allowed to take out a loan -> does not have a Kreditsperre-Modifier and is not insolvent
 */
final class IsPlayerAllowedToTakeOutALoanValidator extends AbstractValidator
{
    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $hasKreditsperre = ModifierCalculator::forStream($gameEvents)->forPlayer($playerId)->modify($gameEvents, HookEnum::KREDITSPERRE, false);
        if ($hasKreditsperre) {
            return new AktionValidationResult(
                canExecute: false,
                reason: "Du darfst diese Konjunkturphase keine Kredite aufnehmen."
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
