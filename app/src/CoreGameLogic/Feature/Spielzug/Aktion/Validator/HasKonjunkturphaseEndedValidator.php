<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseHasEnded;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if the current Konjunkturphase has ended.
 */
final class HasKonjunkturphaseEndedValidator extends AbstractValidator
{
    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        /** @var KonjunkturphaseHasEnded $lastKonjunkturphaseHasEndedEvent */
        $lastKonjunkturphaseHasEndedEvent = $gameEvents->findLastOrNullWhere(
            fn($event) => $event instanceof KonjunkturphaseHasEnded && $event->year->equals(KonjunkturphaseState::getCurrentYear($gameEvents)));
        if ($lastKonjunkturphaseHasEndedEvent === null) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Die aktuelle Konjunkturphase ist noch nicht zu Ende'
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
