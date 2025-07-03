<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasCompletedMoneysheetForCurrentKonjunkturphase;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if the player has successfully completed their money sheet for the current Konjunkturphase.
 * This is not the same as having filled out the money sheet (which is a prerequisite for completing the money sheet)
 * @see HasPlayerFilledOutMoneySheetValidator
 */
final class HasPlayerCompletedMoneySheetValidator extends AbstractValidator
{
    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $lastCompletedMoneysheetEvent = $gameEvents->findLastOrNullWhere(function ($event) use ($playerId, $gameEvents) {
            return $event instanceof PlayerHasCompletedMoneysheetForCurrentKonjunkturphase &&
                $event->playerId->equals($playerId) &&
                KonjunkturphaseState::getCurrentYear($gameEvents)->equals($event->year);
        });

        if ($lastCompletedMoneysheetEvent === null) {
            return new AktionValidationResult(
                canExecute: false,
                reason: "Du musst erst das Money Sheet korrekt ausfüllen"
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
