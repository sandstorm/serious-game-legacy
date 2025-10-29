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
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayerAllowedToTakeOutALoanValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayerNotInsolventValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\LoanData;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanForPlayerWasCorrected;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanForPlayerWasEntered;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanWasTakenOutForPlayer;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use RuntimeException;

class TakeOutALoanForPlayerAktion extends Aktion
{
    /**
     * TakeOutALoanForm can be null in case we want to validate if a player is in general allowed to take out a loan
     * (e.g. if the player is insolvent or has a Kreditsperre they are not allowed to take out a loan).
     * @param TakeOutALoanForm|null $takeOutALoanForm
     */
    public function __construct(
        private readonly TakeOutALoanForm|null $takeOutALoanForm = null
    ) {}

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validator = new IsPlayerAllowedToTakeOutALoanValidator();
        $validator->setNext(new IsPlayerNotInsolventValidator());
        return $validator->validate($gameEvents, $playerId);
    }

    private function isFormInputCorrect(): bool
    {
        try {
            $this->takeOutALoanForm?->validate();
            return true;
        } catch (\Exception $e) {
            return false;
        }
        // TODO beim Aussetzen nicht erlaubt?
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        if ($this->takeOutALoanForm === null) {
            throw new RuntimeException('Cannot take out a loan: TakeOutALoanForm is null.');
        }

        $validationResult = $this->validate($playerId, $gameEvents);
        if (!$validationResult->canExecute) {
            throw new RuntimeException('Cannot take out a loan: ' . $validationResult->reason, 1756200359);
        }

        if (
            $this->takeOutALoanForm->loanAmount === null ||
            $this->takeOutALoanForm->totalRepayment === null ||
            $this->takeOutALoanForm->repaymentPerKonjunkturphase === null
        ) {
            // This should never happen, since $this->takeOutALoanForm->validate() should catch this
            throw new \RuntimeException("Required fields must not be empty");
        }

        $inputLoanData = new LoanData(
            loanAmount: new MoneyAmount($this->takeOutALoanForm->loanAmount),
            totalRepayment: new MoneyAmount($this->takeOutALoanForm->totalRepayment),
            repaymentPerKonjunkturphase: new MoneyAmount($this->takeOutALoanForm->repaymentPerKonjunkturphase)
        );

        $expectedLoanAmount = min($this->takeOutALoanForm->loanAmount, LoanCalculator::getMaxLoanAmount($this->takeOutALoanForm->sumOfAllAssets, $this->takeOutALoanForm->salary, $this->takeOutALoanForm->obligations, $this->takeOutALoanForm->wasPlayerInsolventInThePast)->value);
        $expectedLoanData = new LoanData(
            loanAmount: new MoneyAmount($expectedLoanAmount),
            totalRepayment: new MoneyAmount(LoanCalculator::getCalculatedTotalRepayment($expectedLoanAmount, $this->takeOutALoanForm->zinssatz)),
            repaymentPerKonjunkturphase: new MoneyAmount(LoanCalculator::getCalculatedRepaymentPerKonjunkturphase($expectedLoanAmount, $this->takeOutALoanForm->zinssatz))
        );

        $loanId = new LoanId($this->takeOutALoanForm->loanId);
        $isFormInputCorrect = $this->isFormInputCorrect();

        $returnEvents = GameEventsToPersist::with(
            new LoanForPlayerWasEntered(
                playerId: $playerId,
                loanId: $loanId,
                loanInput: $inputLoanData,
                expectedLoan: $expectedLoanData,
                wasInputCorrect: $isFormInputCorrect,
            )
        );

        $previousTries = MoneySheetState::getNumberOfTriesForLoanInput($gameEvents, $playerId, $loanId);
        if ($previousTries >= Configuration::MAX_NUMBER_OF_TRIES_PER_INPUT - 1 && !$isFormInputCorrect) {
            return $returnEvents->withAppendedEvents(
                new LoanForPlayerWasCorrected(
                    $playerId,
                    $loanId,
                    $expectedLoanData
                )
            );
        }

        // If the input was correct, we take out the loan
        if ($isFormInputCorrect) {
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
