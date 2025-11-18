<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Moneysheet\State\LoanCalculator;
use Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanId;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayerAllowedToTakeOutALoanValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayerNotInsolventValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LoanData;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanWasTakenOutForPlayer;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Konjunkturphase\ValueObject\AuswirkungScopeEnum;
use RuntimeException;

class TakeOutALoanForPlayerAktion extends Aktion
{
    public function __construct(
        private readonly int|null $loanAmount = null
    ) {}

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validator = new IsPlayerAllowedToTakeOutALoanValidator();
        $validator->setNext(new IsPlayerNotInsolventValidator());
        return $validator->validate($gameEvents, $playerId);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $validationResult = $this->validate($playerId, $gameEvents);
        if (!$validationResult->canExecute) {
            throw new RuntimeException('Cannot take out a loan: ' . $validationResult->reason, 1756200359);
        }

        $zinssatz = KonjunkturphaseState::getCurrentKonjunkturphase($gameEvents)->getAuswirkungByScope(AuswirkungScopeEnum::LOANS_INTEREST_RATE)->value;

        // Should not happen due to validation, but to be safe we check it here as well
        if ($this->loanAmount === null) {
            throw new RuntimeException('Loan amount must be set to take out a loan.', 1756200360);
        }

        $inputLoanData = new LoanData(
            loanAmount: new MoneyAmount($this->loanAmount),
            totalRepayment: new MoneyAmount(LoanCalculator::getCalculatedTotalRepayment($this->loanAmount, $zinssatz)),
            repaymentPerKonjunkturphase: new MoneyAmount(LoanCalculator::getCalculatedRepaymentPerKonjunkturphase($this->loanAmount, $zinssatz))
        );

        $loanId = LoanId::unique();

        return GameEventsToPersist::with(
            new LoanWasTakenOutForPlayer(
                playerId: $playerId,
                year: KonjunkturphaseState::getCurrentYear($gameEvents),
                loanId: $loanId,
                loanData: $inputLoanData
            )
        );
    }
}
