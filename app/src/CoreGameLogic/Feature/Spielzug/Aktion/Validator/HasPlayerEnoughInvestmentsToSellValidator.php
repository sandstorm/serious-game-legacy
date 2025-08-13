<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Investments\ValueObject\InvestmentId;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if the player's has enough investments to sell.
 */
final class HasPlayerEnoughInvestmentsToSellValidator extends AbstractValidator
{
    private InvestmentId $investmentId;
    private int $amountToSell;

    public function __construct(
        InvestmentId $stockType,
        int          $amountToSell
    ) {
        $this->investmentId = $stockType;
        $this->amountToSell = $amountToSell;
    }


    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $investmentsToSell = PlayerState::getAmountOfAllInvestmentsOfTypeForPlayer($gameEvents, $playerId, $this->investmentId);

        if ($investmentsToSell < $this->amountToSell) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du hast nicht genug Investitionen vom Typ ' . $this->investmentId->value . ' zum Verkaufen.',
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
