<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use App\Livewire\Forms\TakeOutALoanForm;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Moneysheet\State\LoanCalculator;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanId;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LoanData;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanForPlayerWasCorrected;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanForPlayerWasEntered;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanWasTakenOutForPlayer;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;

class TakeOutALoanForPlayerAktion extends Aktion
{
    public function __construct(
        private readonly TakeOutALoanForm $takeOutALoanForm
    ) {
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        try {
            $this->takeOutALoanForm->validate();
            return new AktionValidationResult(
                canExecute: true,
            );
        } catch (\Exception $e) {
            return new AktionValidationResult(
                canExecute: false,
            );
        }
        // TODO beim Aussetzen nicht erlaubt?
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $validationResult = $this->validate($playerId, $gameEvents);

        $inputLoanData = new LoanData(
            loanAmount: new MoneyAmount($this->takeOutALoanForm->loanAmount),
            totalRepayment: new MoneyAmount($this->takeOutALoanForm->totalRepayment),
            repaymentPerKonjunkturphase: new MoneyAmount($this->takeOutALoanForm->repaymentPerKonjunkturphase)
        );

        $expectedLoanAmount = min($this->takeOutALoanForm->loanAmount, LoanCalculator::getMaxLoanAmount($this->takeOutALoanForm->guthaben, $this->takeOutALoanForm->hasJob));
        $expectedLoanData = new LoanData(
            loanAmount: new MoneyAmount($expectedLoanAmount),
            totalRepayment: new MoneyAmount(LoanCalculator::getCalculatedTotalRepayment($expectedLoanAmount, $this->takeOutALoanForm->zinssatz)),
            repaymentPerKonjunkturphase: new MoneyAmount(LoanCalculator::getCalculatedRepaymentPerKonjunkturphase($expectedLoanAmount, $this->takeOutALoanForm->zinssatz))
        );

        $loanId = new LoanId($this->takeOutALoanForm->loanId);

        $returnEvents = GameEventsToPersist::with(
            new LoanForPlayerWasEntered(
                playerId: $playerId,
                loanId: $loanId,
                loanInput: $inputLoanData,
                expectedLoan: $expectedLoanData,
                wasInputCorrect: $validationResult->canExecute
            )
        );

        $previousTries = MoneySheetState::getNumberOfTriesForLoanInput($gameEvents, $playerId, $loanId);
        if ($previousTries >= Configuration::MAX_NUMBER_OF_TRIES_PER_INPUT - 1 && !$validationResult->canExecute) {
            return $returnEvents->withAppendedEvents(
                new LoanForPlayerWasCorrected(
                    $playerId,
                    $loanId,
                    $expectedLoanData
                )
            );
        }

        // If the input was correct, we take out the loan
        if ($validationResult->canExecute) {
            return $returnEvents->withAppendedEvents(
                new LoanWasTakenOutForPlayer(
                    playerId: $playerId,
                    year: KonjunkturphaseState::getCurrentYear($gameEvents),
                    loanId: $loanId,
                    loanData: $inputLoanData
                )
            );
        }

        return $returnEvents;
    }
}
