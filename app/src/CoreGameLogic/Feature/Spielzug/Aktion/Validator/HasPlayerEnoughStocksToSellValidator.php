<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\AktionsCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if the player's has enough stocks to sell.
 */
final class HasPlayerEnoughStocksToSellValidator extends AbstractValidator
{
    private StockType $stockType;
    private int $amountToSell;

    public function __construct(
        StockType $stockType,
        int $amountToSell
    ) {
        $this->stockType = $stockType;
        $this->amountToSell = $amountToSell;
    }


    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $stocksToSell = PlayerState::getAmountOfAllStocksOfTypeForPlayer($gameEvents, $playerId, $this->stockType);

        if ($stocksToSell < $this->amountToSell) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast nicht genug Aktien vom Typ ' . $this->stockType->toPrettyString() . ' zum Verkaufen.',
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
