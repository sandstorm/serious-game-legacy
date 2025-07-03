<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if the player filled out all required fields in the money sheet. This is not the same as having _completed_
 * the money sheet, which is an extra event and has it's own validator
 * @see HasPlayerCompletedMoneySheetValidator
 */
final class HasPlayerFilledOutMoneySheetValidator extends AbstractValidator
{
    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        if (MoneySheetState::doesMoneySheetRequirePlayerAction($gameEvents, $playerId)) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du musst erst dein Money Sheet korrekt ausfüllen'
            );
        }
        return parent::validate($gameEvents, $playerId);
    }
}
