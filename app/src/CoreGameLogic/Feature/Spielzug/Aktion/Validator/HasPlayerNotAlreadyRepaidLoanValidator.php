<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanId;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if the player has not yet repaid this loan
 */
final class HasPlayerNotAlreadyRepaidLoanValidator extends AbstractValidator
{
    private LoanId $loanId;

    public function __construct(LoanId $loanId)
    {
        $this->loanId = $loanId;
    }

    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        $openRepaymentValue = MoneySheetState::getOpenRepaymentValueForLoan($gameEvents, $playerId, $this->loanId);

        if ($openRepaymentValue->value <= 0) {
            return new AktionValidationResult(
                canExecute: false,
                reason: "Du hast diesen Kredit bereits zurückgezahlt"
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
