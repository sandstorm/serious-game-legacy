<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanWasTakenOutForPlayer;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

class TakeOutALoanForPlayerAktion extends Aktion
{
    private string $intendedUse;
    private MoneyAmount $loanAmount;
    private MoneyAmount $totalRepayment;
    private MoneyAmount $repaymentPerKonjunkturphase;

    public function __construct(
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
                intendedUse: $this->intendedUse,
                loanAmount: $this->loanAmount,
                totalRepayment: $this->totalRepayment,
                repaymentPerKonjunkturphase: $this->repaymentPerKonjunkturphase,
            )
        );
    }
}
