<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\InvestmentsWereBoughtForPlayer;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if another player has bought stocks of the same type this turn.
 */
final class HasAnotherPlayerBoughtInvestmentsThisTurnValidator extends AbstractValidator
{
    private InvestmentId $investmentId;

    public function __construct(
        InvestmentId $investmentId,
    ) {
        $this->investmentId = $investmentId;
    }


    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $hasAnotherPlayerBoughtStocksThisTurn = GamePhaseState::anotherPlayerHasBoughtInvestmentsThisTurn($gameEvents, $playerId);
        $investmentsBought = $gameEvents->findLastOrNull(InvestmentsWereBoughtForPlayer::class)?->investmentId;

        if (!$hasAnotherPlayerBoughtStocksThisTurn || $investmentsBought !== $this->investmentId) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Ein anderer Spieler muss Investitionen der gleichen Art gekauft haben, bevor du welche verkaufen kannst.',
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
