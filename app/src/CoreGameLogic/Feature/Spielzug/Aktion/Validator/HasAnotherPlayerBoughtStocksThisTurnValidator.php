<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\StocksWereBoughtForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if another player has bought stocks of the same type this turn.
 */
final class HasAnotherPlayerBoughtStocksThisTurnValidator extends AbstractValidator
{
    private StockType $stockType;

    public function __construct(
        StockType $stockType,
    ) {
        $this->stockType = $stockType;
    }


    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $hasAnotherPlayerBoughtStocksThisTurn = GamePhaseState::anotherPlayerHasBoughtStocksThisTurn($gameEvents, $playerId);
        $stockTypeOfTheEvent = $gameEvents->findLastOrNull(StocksWereBoughtForPlayer::class)?->stockType;

        if (!$hasAnotherPlayerBoughtStocksThisTurn || $stockTypeOfTheEvent !== $this->stockType) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Ein anderer Spieler muss Aktien der gleichen Art gekauft haben, bevor du welche verkaufen kannst.',
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
