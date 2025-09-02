<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if the player has a negative Balance.
 * This is a requirement for Insolvenz.
 */
final class HasPlayerANegativeBalanceValidator extends AbstractValidator
{
    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {

        if (PlayerState::getGuthabenForPlayer($gameEvents, $playerId)->value >= 0) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Dein Kontostand ist positiv'
            );
        }
        return parent::validate($gameEvents, $playerId);
    }
}
