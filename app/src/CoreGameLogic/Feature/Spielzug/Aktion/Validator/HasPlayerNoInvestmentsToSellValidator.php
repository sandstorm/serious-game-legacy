<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if the player has no investments to sell.
 */
final class HasPlayerNoInvestmentsToSellValidator extends AbstractValidator
{
    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {

        if (PlayerState::getTotalValueOfAllAssetsForPlayer($gameEvents, $playerId)->value > 0) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast noch Geldanlagen, die du verkaufen kannst'
            );
        }
        return parent::validate($gameEvents, $playerId);
    }
}
