<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanId;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerANegativeBalanceValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerCompletedMoneySheetValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerNotAlreadyRepaidLoanValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanWasRepaidForPlayerInCaseOfInsolvenz;
use Domain\CoreGameLogic\PlayerId;
use RuntimeException;

class RepayLoanForPlayerInCaseOfInsolvenzAktion extends Aktion
{
    public function __construct(
        private readonly LoanId $loanId,
    ) {
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validatorChain = new HasPlayerNotAlreadyRepaidLoanValidator($this->loanId);
        $validatorChain
            ->setNext(new HasPlayerANegativeBalanceValidator())
            ->setNext(new HasPlayerCompletedMoneySheetValidator());
        return $validatorChain->validate($gameEvents, $playerId);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $validationResult = $this->validate($playerId, $gameEvents);
        if (!$validationResult->canExecute) {
            throw new RuntimeException('' . $validationResult->reason, 1757597278);
        }

        return GameEventsToPersist::with(
            new LoanWasRepaidForPlayerInCaseOfInsolvenz($playerId, $this->loanId)
        );
    }
}
