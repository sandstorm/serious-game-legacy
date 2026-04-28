<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if the player has already started their turn.
 *
 * Use this in the validator chain of any Aktion that should only run once the player
 * has clicked through the "Du bist am Zug!" mandatory modal. Prevents turn-actions
 * from slipping through when the popup did not appear (see issue #652).
 */
final class HasPlayerStartedSpielzugValidator extends AbstractValidator
{
    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        if (!GamePhaseState::hasPlayerStartedTurn($gameEvents, $playerId)) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du musst deinen Spielzug erst starten.',
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
