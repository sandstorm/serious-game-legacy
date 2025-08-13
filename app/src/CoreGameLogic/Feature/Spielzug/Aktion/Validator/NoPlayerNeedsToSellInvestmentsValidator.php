<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasEnded;
use Domain\CoreGameLogic\Feature\Spielzug\Event\InvestmentsWereBoughtForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if the player if no player needs to sell stocks this turn.
 */
final class NoPlayerNeedsToSellInvestmentsValidator extends AbstractValidator
{
    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $eventsThisTurn = $gameEvents->findAllAfterLastOfTypeOrNull(SpielzugWasEnded::class)
            ?? $gameEvents->findAllAfterLastOfType(GameWasStarted::class);

        $stocksWereBought = $eventsThisTurn->findLastOrNull(InvestmentsWereBoughtForPlayer::class);

        // if no stocks were bought, we can skip this validation
        if ($stocksWereBought === null) {
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
