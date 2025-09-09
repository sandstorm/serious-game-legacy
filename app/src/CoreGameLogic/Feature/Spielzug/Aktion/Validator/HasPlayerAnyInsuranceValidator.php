<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if the player has any insurance.
 */
final class HasPlayerAnyInsuranceValidator extends AbstractValidator
{
    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {

        if (MoneySheetState::getCostOfAllInsurances($gameEvents, $playerId)->value <= 0) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast keine Versicherung'
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
