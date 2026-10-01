<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Moneysheet\State\LoanCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\PlayerId;

/**
 * Succeeds if the loan amount is positive and does not exceed the credit limit of the player.
 */
final class IsLoanAmountWithinLimitValidator extends AbstractValidator
{
    public function __construct(private readonly int $loanAmount)
    {
    }

    public function validate(GameEvents $gameEvents, PlayerId $playerId): AktionValidationResult
    {
        if ($this->loanAmount < 1) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Der Kreditbetrag muss größer als 0 sein.',
            );
        }

        if ($this->loanAmount > LoanCalculator::getMaxLoanAmountForPlayer($gameEvents, $playerId)->value) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du kannst keinen Kredit aufnehmen, der höher ist als das Kreditlimit.',
            );
        }

        return parent::validate($gameEvents, $playerId);
    }
}
