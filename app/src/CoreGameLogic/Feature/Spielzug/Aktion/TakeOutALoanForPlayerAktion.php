<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\ValueObject\CurrentYear;
use Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanId;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanWasTakenOutForPlayer;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

class TakeOutALoanForPlayerAktion extends Aktion
{
    private CurrentYear $currentYear;
    private LoanId $loanId;
    private string $intendedUse;
    private MoneyAmount $loanAmount;
    private MoneyAmount $totalRepayment;
    private MoneyAmount $repaymentPerKonjunkturphase;

    public function __construct(
        CurrentYear $currentYear,
        LoanId $loanId,
        string $intendedUse,
        MoneyAmount $loanAmount,
        MoneyAmount $totalRepayment,
        MoneyAmount $repaymentPerKonjunkturphase,
    ) {
        parent::__construct('take-out-a-loan', 'Kredit aufnehmen');
        $this->intendedUse = $intendedUse;
        $this->loanAmount = $loanAmount;
        $this->totalRepayment = $totalRepayment;
        $this->repaymentPerKonjunkturphase = $repaymentPerKonjunkturphase;
        $this->currentYear = $currentYear;
        $this->loanId = $loanId;
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        return new AktionValidationResult(
            canExecute: true,
        );
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('Cannot take out a loan: ' . $result->reason, 1751554652);
        }

        return GameEventsToPersist::with(
            new LoanWasTakenOutForPlayer(
                playerId: $playerId,
                year: $this->currentYear,
                loanId: $this->loanId,
                intendedUse: $this->intendedUse,
                loanAmount: $this->loanAmount,
                totalRepayment: $this->totalRepayment,
                repaymentPerKonjunkturphase: $this->repaymentPerKonjunkturphase,
            )
        );
    }
}
