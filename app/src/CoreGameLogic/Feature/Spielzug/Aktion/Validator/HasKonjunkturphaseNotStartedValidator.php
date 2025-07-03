<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasStartedKonjunkturphase;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if the player has not yet started the current Konjunkturphase.
 */
final class HasKonjunkturphaseNotStartedValidator extends AbstractValidator
{
    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        /** @var PlayerHasStartedKonjunkturphase $lastStartKonjunkturphaseEvent */
        $lastStartKonjunkturphaseEvent = $gameEvents->findLastOrNullWhere(
            fn($event) => $event instanceof PlayerHasStartedKonjunkturphase && $event->playerId->equals($playerId));
        if ( // has player already started this Konjunkturphase?
            $lastStartKonjunkturphaseEvent !== null &&
            $lastStartKonjunkturphaseEvent->year->equals(KonjunkturphaseState::getCurrentYear($gameEvents))
        ) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast diese Konjunkturphase bereits gestartet'
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
