<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Moneysheet\State\LoanCalculator;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanId;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerEnoughResourcesValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerNotAlreadyRepaidLoanValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanWasRepaidForPlayer;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use RuntimeException;

class RepayLoanForPlayerAktion extends Aktion
{
    public function __construct(
        private readonly LoanId $loanId,
    ) {
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $resourceChanges = $this->getCostsForLoanRepayment($playerId, $gameEvents, $this->loanId);

        $validatorChain = new HasPlayerNotAlreadyRepaidLoanValidator($this->loanId);
        $validatorChain->setNext(new HasPlayerEnoughResourcesValidator($resourceChanges));
        return $validatorChain->validate($gameEvents, $playerId);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $validationResult = $this->validate($playerId, $gameEvents);
        if (!$validationResult->canExecute) {
            throw new RuntimeException('' . $validationResult->reason, 1756813341);
        }

        $resourceChanges = $this->getCostsForLoanRepayment($playerId, $gameEvents, $this->loanId);

        return GameEventsToPersist::with(
            new LoanWasRepaidForPlayer($playerId, $this->loanId, $resourceChanges)
        );
    }

    private function getCostsForLoanRepayment(PlayerId $playerId, GameEvents $gameEvents, LoanId $loanId): ResourceChanges
    {
        $repaymentForLoan = MoneySheetState::getOpenRepaymentValueForLoan($gameEvents, $playerId, $loanId);
        $repaymentCost = LoanCalculator::getCostsForLoanRepayment($repaymentForLoan->value);
        return new ResourceChanges(
            guthabenChange: new MoneyAmount($repaymentCost->value * -1)
        );
    }
}
