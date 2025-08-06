<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if the player if player has not interacted with the stocks modal this turn,
 */
final class HasPlayerInteractedWithStocksModalThisTurnValidator extends AbstractValidator
{
    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        if (
            PlayerState::hasPlayerInteractedWithStocksModalThisTurn($gameEvents, $playerId)
        ) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast diesen Spielzug schon Aktien verkauft. Du kannst nicht erneut Aktien verkaufen.'
            );
        }

        return parent::validate($gameEvents, $playerId);
    }

}
