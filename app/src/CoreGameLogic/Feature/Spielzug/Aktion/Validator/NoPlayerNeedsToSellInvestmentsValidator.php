<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if no player needs to interact with the investments modal (i.e. sell investments)
 */
final class NoPlayerNeedsToSellInvestmentsValidator extends AbstractValidator
{
    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $playerBoughtOrSoldInvestmentsThisTurn = GamePhaseState::playerBoughtOrSoldInvestmentsThisTurn($gameEvents, $playerId);

        // if no stocks were bought/sold, we can skip this validation
        if (!$playerBoughtOrSoldInvestmentsThisTurn) {
            return parent::validate($gameEvents, $playerId);
        }

        $players = GamePhaseState::getOrderedPlayers($gameEvents);
        $allOtherPlayersInteractedWithStocksModal = true;
        foreach ($players as $otherPlayer) {
            if ($otherPlayer->equals($playerId)) {
                continue; // skip the current player
            }

            if (!PlayerState::hasPlayerInteractedWithInvestmentsModalThisTurn($gameEvents, $otherPlayer)) {
                $allOtherPlayersInteractedWithStocksModal = false;
                break; // at least one player has not sold their stocks
            }
        }

        if (
            !$allOtherPlayersInteractedWithStocksModal
        ) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du kannst deinen Spielzug nicht beenden. Andere Spieler können noch Investitionen verkaufen.'
            );
        }

        return parent::validate($gameEvents, $playerId);
    }

}
