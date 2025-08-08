<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if the player has not already started their turn.
 */
final class HasPlayerAlreadyStartedSpielzugValidator extends AbstractValidator
{
    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $hasPlayerStartedTurn = GamePhaseState::hasPlayerStartedTurn($gameEvents, $playerId);
        if ($hasPlayerStartedTurn) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast bereits deinen Spielzug gestartet.',
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
