<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\AnswerForWeiterbildungWasSubmitted;
use Domain\CoreGameLogic\Feature\Spielzug\Event\WeiterbildungWasStarted;
use Domain\CoreGameLogic\PlayerId;

final class HasNotYetAnsweredThisWeiterbildungValidator extends AbstractValidator
{
    public function __construct(
    )
    {
    }

    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $eventsAfterWeiterbildungWasStarted = $gameEvents->findAllAfterLastOrNullWhere(function ($event) use ($playerId) {
            return $event instanceof WeiterbildungWasStarted
                && $event->playerId->equals($playerId);
        });

        /**
         * WHY:
         * We do not care for the null case (no WeiterbildungWasStarted event was found for this player) since we handle
         * that in another validator @see HasPlayerStartedAWeiterbildungThisTurnValidator
         * If there is _any_ submit event after the last WeiterbildungWasStarted event, we know that something is wrong.
         */
        if ($eventsAfterWeiterbildungWasStarted !== null &&
            $eventsAfterWeiterbildungWasStarted->findLastOrNull(AnswerForWeiterbildungWasSubmitted::class) !== null) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast für diese Weiterbildung bereits eine Antwort abgegeben'
            );
        }
        return parent::validate($gameEvents, $playerId);
    }
}
