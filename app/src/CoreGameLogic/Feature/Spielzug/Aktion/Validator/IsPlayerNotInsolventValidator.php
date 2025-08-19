<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if the player is not insolvent.
 */
final class IsPlayerNotInsolventValidator extends AbstractValidator
{
    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {

        if (PlayerState::isPlayerInsolvent($gameEvents, $playerId)) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du bist insolvent'
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
