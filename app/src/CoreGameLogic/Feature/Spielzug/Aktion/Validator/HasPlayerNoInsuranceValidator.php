<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Insurance\InsuranceFinder;

/**
 * Succeeds if the player has no insurance.
 * This will be important if the player wants to file for Insolvenz.
 */
final class HasPlayerNoInsuranceValidator extends AbstractValidator
{
    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $insurances = InsuranceFinder::getInstance()->getAllInsurances();

        foreach ($insurances as $insurance) {
            if (MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $playerId, $insurance->id)) {
                // Player does have this insurance, fail
                return new AktionValidationResult(
                    canExecute: false,
                    reason: 'Du hast noch Versicherungen, die du kündigen kannst'
                );
            }
        }

        return parent::validate($gameEvents, $playerId);
    }
}
