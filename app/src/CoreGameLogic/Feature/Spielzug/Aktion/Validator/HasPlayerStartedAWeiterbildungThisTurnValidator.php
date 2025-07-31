<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasEnded;
use Domain\CoreGameLogic\Feature\Spielzug\Event\WeiterbildungWasStarted;
use Domain\CoreGameLogic\PlayerId;

final class HasPlayerStartedAWeiterbildungThisTurnValidator extends AbstractValidator
{
    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $eventsAfterLastWeiterbildungWasStarted = $gameEvents->findAllAfterLastOrNullWhere(
            fn($e) => $e instanceof WeiterbildungWasStarted && $e->playerId->equals($playerId)
        );

        if (
            $eventsAfterLastWeiterbildungWasStarted === null ||
            $eventsAfterLastWeiterbildungWasStarted->findLastOrNull(SpielzugWasEnded::class) !== null) {
            return new AktionValidationResult(
                canExecute:  false,
                reason: 'Du hast diese Runde noch keine Weiterbildung gestartet'
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
