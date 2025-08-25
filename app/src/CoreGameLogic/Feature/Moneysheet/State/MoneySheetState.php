<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Moneysheet\ValueObject\LoanId;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\InputResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\UpdatesInputForLebenshaltungskosten;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\UpdatesInputForLoan;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\UpdatesInputForSteuernUndAbgaben;
use Domain\CoreGameLogic\Feature\Spielzug\Event\InsuranceForPlayerWasCancelled;
use Domain\CoreGameLogic\Feature\Spielzug\Event\InsuranceForPlayerWasConcluded;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobOfferWasAccepted;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LebenshaltungskostenForPlayerWereCorrected;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LebenshaltungskostenForPlayerWereEntered;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanForPlayerWasCorrected;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanForPlayerWasEntered;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LoanWasTakenOutForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasCompletedMoneysheetForCurrentKonjunkturphase;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SteuernUndAbgabenForPlayerWereCorrected;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SteuernUndAbgabenForPlayerWereEntered;
use Domain\CoreGameLogic\Feature\Spielzug\State\ModifierCalculator;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Insurance\InsuranceFinder;
use Domain\Definitions\Insurance\ValueObject\InsuranceId;

class MoneySheetState
{
    public static function calculateLebenshaltungskostenForPlayer(
        GameEvents $gameEvents,
        PlayerId $playerId
    ): MoneyAmount {
        $gehalt = PlayerState::getCurrentGehaltForPlayer($gameEvents, $playerId);
        return new MoneyAmount(max([
            $gehalt->value * self::getPercentageForLebenshaltungskostenForPlayer($gameEvents, $playerId) / 100,
            self::calculateMinimumValueForLebenshaltungskostenForPlayer($gameEvents, $playerId)->value,
            ]
        ));
    }

    /**
     * Returns the modified percent value for the Lebenshaltungskosten. Considers a raw percent increase and modifiers
     * that will be multiplied with the percent value.
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return float
     */
    public static function getPercentageForLebenshaltungskostenForPlayer(GameEvents $gameEvents, PlayerId $playerId): float
    {
        $multiplier = ModifierCalculator::forStream($gameEvents)->forPlayer($playerId)->modify($gameEvents, HookEnum::LEBENSHALTUNGSKOSTEN_MULTIPLIER, 1.0);
        return $multiplier * ModifierCalculator::forStream($gameEvents)->forPlayer($playerId)->modify($gameEvents, HookEnum::LEBENSHALTUNGSKOSTEN_PERCENT_INCREASE, value: Configuration::LEBENSHALTUNGSKOSTEN_PERCENT);
    }

    /**
     * Returns the modified value for the minimum Lebenshaltungskosten. Takes into consideration any modifiers to the
     * Lebenshaltungskosten multiplier and min value. Multiplier will be applied last.
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return MoneyAmount
     */
    public static function calculateMinimumValueForLebenshaltungskostenForPlayer(GameEvents $gameEvents, PlayerId $playerId): MoneyAmount
    {
        $multiplier = ModifierCalculator::forStream($gameEvents)->forPlayer($playerId)->modify($gameEvents, HookEnum::LEBENSHALTUNGSKOSTEN_MULTIPLIER, 1.0);
        return new MoneyAmount(
            ModifierCalculator::forStream($gameEvents)
                ->forPlayer($playerId)
                ->modify(
                    $gameEvents,
                    HookEnum::LEBENSHALTUNGSKOSTEN_MIN_VALUE,
                    value: new MoneyAmount(Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE)
                )->value
            * $multiplier
        );
    }

    public static function calculateSteuernUndAbgabenForPlayer(GameEvents $gameEvents, PlayerId $playerId): MoneyAmount
    {
        $gehalt = PlayerState::getCurrentGehaltForPlayer($gameEvents, $playerId);
        return new MoneyAmount($gehalt->value * Configuration::STEUERN_UND_ABGABEN_MULTIPLIER);
    }

    private static function getEventsSinceLastGehaltChangeForPlayer(
        GameEvents $gameEvents,
        PlayerId $playerId
    ): GameEvents {
        // TODO We may need to change this later (e.g. quit job, modifiers)
        // FIXME this needs to change now with the modifiers
        $eventsAfterLastGehaltChange = $gameEvents->findAllAfterLastOrNullWhere(
            fn($event) => $event instanceof JobOfferWasAccepted && $event->playerId->equals($playerId));
        if ($eventsAfterLastGehaltChange === null) {
            $eventsAfterLastGehaltChange = $gameEvents->findAllAfterLastOfType(GameWasStarted::class);
        }
        return $eventsAfterLastGehaltChange;
    }

    /**
     * Returns the number of tries since the last time the Gehalt changed.
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return int
     */
    public static function getNumberOfTriesForSteuernUndAbgabenInput(GameEvents $gameEvents, PlayerId $playerId): int
    {
        $eventsAfterLastGehaltChange = self::getEventsSinceLastGehaltChangeForPlayer($gameEvents, $playerId);
        // Gather all relevant input events for the player
        $tries = $eventsAfterLastGehaltChange->findAllOfType(SteuernUndAbgabenForPlayerWereEntered::class)
            ->filter(fn(SteuernUndAbgabenForPlayerWereEntered $event) => $event->playerId->equals($playerId));

        return count($tries);
    }

    public static function getNumberOfTriesForLebenshaltungskostenInput(GameEvents $gameEvents, PlayerId $playerId): int
    {
        $eventsAfterLastGehaltChange = self::getEventsSinceLastGehaltChangeForPlayer($gameEvents, $playerId);
        $tries = $eventsAfterLastGehaltChange->findAllOfType(LebenshaltungskostenForPlayerWereEntered::class)
            ->filter(fn(LebenshaltungskostenForPlayerWereEntered $event) => $event->playerId->equals($playerId));

        return count($tries);
    }

    public static function getNumberOfTriesForLoanInput(GameEvents $gameEvents, PlayerId $playerId, LoanId $loanId): int
    {
        $tries = $gameEvents->findAllOfType(LoanForPlayerWasEntered::class)
            ->filter(fn(LoanForPlayerWasEntered $event) =>
                $event->playerId->equals($playerId)
                && $event->loanId->equals($loanId)
            );

        return count($tries);
    }

    public static function getResultOfLastSteuernUndAbgabenInput(
        GameEvents $gameEvents,
        PlayerId $playerId
    ): InputResult {
        $lastInputEventForPlayer = $gameEvents->findLastOrNullWhere(
            fn($event) => $event instanceof UpdatesInputForSteuernUndAbgaben && $event->getPlayerId()->equals($playerId));

        if ($lastInputEventForPlayer === null) {
            // No error message before first input
            return new InputResult(wasSuccessful: true);
        }

        if ($lastInputEventForPlayer instanceof SteuernUndAbgabenForPlayerWereEntered) {
            return new InputResult($lastInputEventForPlayer->wasInputCorrect());
        } // after this we know that the input was NOT successful

        if ($lastInputEventForPlayer instanceof SteuernUndAbgabenForPlayerWereCorrected) {
            // multiply resource change with -1 to get a positive value
            return new InputResult(false, new MoneyAmount(-1 * $lastInputEventForPlayer->getResourceChanges($playerId)->guthabenChange->value));
        }
        throw new \RuntimeException("Unknown Event type " . $lastInputEventForPlayer::class);
    }

    public static function getResultOfLastLebenshaltungskostenInput(
        GameEvents $gameEvents,
        PlayerId $playerId
    ): InputResult {
        $lastInputEventForPlayer = $gameEvents->findLastOrNullWhere(
            fn($event) => $event instanceof UpdatesInputForLebenshaltungskosten
                && $event->getPlayerId()->equals($playerId));

        if ($lastInputEventForPlayer === null) {
            // No error message before first input
            return new InputResult(wasSuccessful: true);
        }

        if ($lastInputEventForPlayer instanceof LebenshaltungskostenForPlayerWereEntered) {
            return new InputResult($lastInputEventForPlayer->wasInputCorrect());
        } // after this we know that the input was NOT successful

        if ($lastInputEventForPlayer instanceof LebenshaltungskostenForPlayerWereCorrected) {
            // multiply resource change with -1 to get a positive value
            return new InputResult(
                false,
                new MoneyAmount(-1 * $lastInputEventForPlayer->getResourceChanges($playerId)->guthabenChange->value)
            );
        }
        throw new \RuntimeException("Unknown Event type " . $lastInputEventForPlayer::class);
    }

    public static function getResultOfLastLoanInput(
        GameEvents $gameEvents,
        PlayerId $playerId,
        LoanId $loanId
    ): InputResult {
        $lastInputEventForPlayer = $gameEvents->findLastOrNullWhere(
            fn($event) => $event instanceof UpdatesInputForLoan
                && $event->getPlayerId()->equals($playerId)
                && $event->getLoanId()->equals($loanId)
        );

        if ($lastInputEventForPlayer === null) {
            // No error message before first input
            return new InputResult(wasSuccessful: true);
        }

        if ($lastInputEventForPlayer instanceof LoanForPlayerWasEntered) {
            return new InputResult($lastInputEventForPlayer->wasInputCorrect());
        } // after this we know that the input was NOT successful

        if ($lastInputEventForPlayer instanceof LoanForPlayerWasCorrected) {
            // multiply resource change with -1 to get a positive value
            return new InputResult(
                false,
                new MoneyAmount(-1 * $lastInputEventForPlayer->getResourceChanges($playerId)->guthabenChange->value)
            );
        }
        throw new \RuntimeException("Unknown Event type " . $lastInputEventForPlayer::class);
    }

    public static function getLastInputForSteuernUndAbgaben(GameEvents $gameEvents, PlayerId $myself): MoneyAmount
    {
        /** @var UpdatesInputForSteuernUndAbgaben|null $lastInputEvent @phpstan-ignore varTag.type */
        $lastInputEvent = $gameEvents->findLastOrNullWhere(
            fn($event) => $event instanceof UpdatesInputForSteuernUndAbgaben && $event->getPlayerId()->equals($myself));
        return $lastInputEvent === null ? new MoneyAmount(0) : $lastInputEvent->getUpdatedValue();
    }

    public static function getLastInputForLebenshaltungskosten(GameEvents $gameEvents, PlayerId $myself): MoneyAmount
    {
        /** @var UpdatesInputForLebenshaltungskosten|null $lastInputEvent @phpstan-ignore varTag.type */
        $lastInputEvent = $gameEvents->findLastOrNullWhere(
            fn($event) => $event instanceof UpdatesInputForLebenshaltungskosten
                && $event->getPlayerId()->equals($myself));
        return $lastInputEvent === null ? new MoneyAmount(0) : $lastInputEvent->getUpdatedValue();
    }

    /**
     * Returns true, if the player needs to change the input for Steuern und Abgaben.
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return bool
     */
    public static function doesSteuernUndAbgabenRequirePlayerAction(GameEvents $gameEvents, PlayerId $playerId): bool
    {
        /** @var UpdatesInputForSteuernUndAbgaben|null $lastInputEvent @phpstan-ignore varTag.type */
        $lastInputEvent = $gameEvents->findLastOrNullWhere(
            fn($event) => $event instanceof UpdatesInputForSteuernUndAbgaben
                && $event->getPlayerId()->equals($playerId));
        if ($lastInputEvent === null) {
            // There has not been any player input for this field
            // -> the current value is the default value
            // -> check if the default value is also the correct value
            // Return true, if the default value does NOT match the expected value
            return !self::calculateSteuernUndAbgabenForPlayer($gameEvents, $playerId)
                ->equals(Configuration::STEUERN_UND_ABGABEN_DEFAULT_VALUE);
        }
        // Return true, if the last input does NOT match the expected value
        return !self::calculateSteuernUndAbgabenForPlayer($gameEvents, $playerId)
            ->equals($lastInputEvent->getUpdatedValue());
    }

    /**
     * Returns true, if the player needs to change the input for Lebenshaltungskosten.
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return bool
     */
    public static function doesLebenshaltungskostenRequirePlayerAction(GameEvents $gameEvents, PlayerId $playerId): bool
    {
        /** @var UpdatesInputForLebenshaltungskosten|null $lastInputEvent @phpstan-ignore varTag.type */
        $lastInputEvent = $gameEvents->findLastOrNullWhere(
            fn($event) => $event instanceof UpdatesInputForLebenshaltungskosten
                && $event->getPlayerId()->equals($playerId));
        if ($lastInputEvent === null) {
            // There has not been any player input for this field
            // -> the current value is the default value
            // -> check if the default value is also the correct value
            // Return true, if the default value does NOT match the expected value
            return !self::calculateLebenshaltungskostenForPlayer($gameEvents, $playerId)
                ->equals(Configuration::LEBENSHALTUNGSKOSTEN_DEFAULT_VALUE);
        }
        // Return true, if the last input does NOT match the expected value
        return !self::calculateLebenshaltungskostenForPlayer($gameEvents, $playerId)
            ->equals($lastInputEvent->getUpdatedValue());
    }

    public static function hasPlayerCompletedMoneysheet(GameEvents $gameEvents, PlayerId $playerId): bool
    {
        $lastPlayerHasCompletedMoneysheetEvent = $gameEvents->findLastOrNullWhere(function ($event) use ($playerId, $gameEvents) {
            return $event instanceof PlayerHasCompletedMoneysheetForCurrentKonjunkturphase &&
                $event->playerId->equals($playerId) &&
                $event->year->equals(KonjunkturphaseState::getCurrentYear($gameEvents));
        });
        return $lastPlayerHasCompletedMoneysheetEvent !== null;
    }

    public static function doesMoneySheetRequirePlayerAction(GameEvents $gameEvents, PlayerId $playerId): bool
    {
        return self::doesLebenshaltungskostenRequirePlayerAction($gameEvents, $playerId)
            || self::doesSteuernUndAbgabenRequirePlayerAction($gameEvents, $playerId);
    }

    public static function doesPlayerHaveThisInsurance(GameEvents $gameEvents, PlayerId $playerId, InsuranceId $insuranceId): bool
    {
        // returns all events after the last insurance conclusion event for this player
        // if no conclusion event was found, it returns null
        // if no events after the conclusion event were found, it returns an empty array
        $eventsAfterInsuranceWasConcluded = $gameEvents->findAllAfterLastOrNullWhere(
            fn($event) => $event instanceof InsuranceForPlayerWasConcluded &&
                $event->playerId->equals($playerId) &&
                $event->insuranceId === $insuranceId
        );

        if ($eventsAfterInsuranceWasConcluded === null) {
            // if this is null, the player never took out an insurance
            return false;
        }
        if (count($eventsAfterInsuranceWasConcluded->events) > 0) {
            // if there are any cancellation events after the conclusion event, the insurance is not active anymore
            $lastCancellationEvent = $eventsAfterInsuranceWasConcluded->findLastOrNullWhere(
                fn($event) => $event instanceof InsuranceForPlayerWasCancelled &&
                    $event->playerId->equals($playerId) &&
                    $event->insuranceId === $insuranceId
            );
            if ($lastCancellationEvent !== null) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns the total cost of all insurances concluded by the player.
     *
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return MoneyAmount
     */
    public static function getCostOfAllInsurances(GameEvents $gameEvents, PlayerId $playerId): MoneyAmount
    {
        $insurances = InsuranceFinder::getInstance()->getAllInsurances();
        $currentPlayerPhase = PlayerState::getCurrentLebenszielphaseIdForPlayer($gameEvents, $playerId)->value;

        $totalCost = new MoneyAmount(0);
        foreach ($insurances as $insurance) {
            if (!self::doesPlayerHaveThisInsurance($gameEvents, $playerId, $insurance->id)) {
                // Player does not have this insurance, skip it
                continue;
            }
            if (PlayerState::hasPlayerPayedForInsuranceThisKonjunkturphase($gameEvents, $playerId, $insurance->id)) {
                // Player already payed for it this year, skip it
                continue;
            }
            $totalCost = $totalCost->add($insurance->getAnnualCost($currentPlayerPhase));
        }

        return $totalCost;
    }

    /**
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return LoanWasTakenOutForPlayer[]
     */
    public static function getLoansForPlayer(GameEvents $gameEvents, PlayerId $playerId): array
    {
        return $gameEvents->findAllOfType(LoanWasTakenOutForPlayer::class)
            ->filter(fn (LoanWasTakenOutForPlayer $event) => $event->playerId->equals($playerId));
    }

    /**
     * Returns the amount of open rates for a specific loan.
     *
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @param LoanId $loanId
     * @return int
     */
    public static function getOpenRatesForLoan(GameEvents $gameEvents, PlayerId $playerId, LoanId $loanId): int
    {
        /** @var LoanWasTakenOutForPlayer|null $loan */
        $loan = $gameEvents->findLastOrNullWhere(
            fn($event) => $event instanceof LoanWasTakenOutForPlayer &&
                $event->playerId->equals($playerId) &&
                $event->loanId->equals($loanId)
        );

        if ($loan === null) {
            throw new \RuntimeException("No loan found for player {$playerId->value} with ID {$loanId->value}");
        }

        // Calculate the open rates based on the total repayment and the repayment per Konjunkturphase
        $yearOfTheLoan = $loan->year->value;
        $year = GamePhaseState::currentKonjunkturphasenYear($gameEvents)->value;

        return max(Configuration::REPAYMENT_PERIOD - ($year - $yearOfTheLoan), 0);
    }

    /**
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return MoneyAmount
     */
    public static function getSumOfAllLoansForPlayer(GameEvents $gameEvents, PlayerId $playerId): MoneyAmount
    {
        $loans = self::getLoansForPlayer($gameEvents, $playerId);
        $sum = 0;
        foreach ($loans as $loan) {
            $sum += $loan->loanData->loanAmount->value;
        }
        return new MoneyAmount($sum);
    }

    public static function getTotalRepaymentValueForAllLoans(GameEvents $gameEvents, PlayerId $playerId): MoneyAmount
    {
        $loans = self::getLoansForPlayer($gameEvents, $playerId);
        $total = 0;
        foreach ($loans as $loan) {
            $total = $total + $loan->loanData->totalRepayment->value;
        }
        return new MoneyAmount($total);
    }

    /**
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return MoneyAmount
     */
    public static function getAnnualExpensesForAllLoans(GameEvents $gameEvents, PlayerId $playerId): MoneyAmount
    {
        $annualExpenses = new MoneyAmount(0);
        $loans = MoneySheetState::getLoansForPlayer($gameEvents, $playerId);
        foreach ($loans as $loan) {
            if (MoneySheetState::getOpenRatesForLoan($gameEvents, $playerId, $loan->loanId) > 0) {
                $annualExpenses = $annualExpenses->add($loan->loanData->repaymentPerKonjunkturphase);
            }
        }
        return $annualExpenses;
    }

    /**
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return MoneyAmount
     */
    public static function getAnnualExpensesForPlayer(GameEvents $gameEvents, PlayerId $playerId): MoneyAmount
    {
        $annualExpenses = (new MoneyAmount(0))
            ->add(self::getAnnualExpensesForAllLoans($gameEvents, $playerId))
            ->add(self::getCostOfAllInsurances($gameEvents, $playerId))
            ->add(self::calculateSteuernUndAbgabenForPlayer($gameEvents, $playerId))
            ->add(self::calculateLebenshaltungskostenForPlayer($gameEvents, $playerId));

        return $annualExpenses;
    }

    /**
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return MoneyAmount
     */
    public static function calculateAnnualExpensesFromPlayerInput(GameEvents $gameEvents, PlayerId $playerId): MoneyAmount
    {
        $annualExpensesFromPlayerInput = (new MoneyAmount(0))
            ->add(self::getAnnualExpensesForAllLoans($gameEvents, $playerId))
            ->add(self::getCostOfAllInsurances($gameEvents, $playerId))
            ->add(self::getLastInputForLebenshaltungskosten($gameEvents, $playerId))
            ->add(self::getLastInputForSteuernUndAbgaben($gameEvents, $playerId));

        return $annualExpensesFromPlayerInput;
    }

    /**
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return MoneyAmount
     */
    public static function getAnnualIncomeForPlayer(GameEvents $gameEvents, PlayerId $playerId): MoneyAmount
    {
        $annualIncome = (new MoneyAmount(0))
            ->add(PlayerState::getCurrentGehaltForPlayer($gameEvents, $playerId))
            ->add(PlayerState::getDividendForAllStocksForPlayer($gameEvents, $playerId));

        return $annualIncome;
    }

    public static function calculateTotalForPlayer(GameEvents $gameEvents, PlayerId $playerId): MoneyAmount
    {
        return self::getAnnualIncomeForPlayer($gameEvents, $playerId)
            ->subtract(self::getAnnualExpensesForPlayer($gameEvents, $playerId));
    }

    public static function calculateTotalFromPlayerInput(GameEvents $gameEvents, PlayerId $playerId): MoneyAmount
    {
        return self::getAnnualIncomeForPlayer($gameEvents, $playerId)
            ->subtract(self::calculateAnnualExpensesFromPlayerInput($gameEvents, $playerId));
    }
}
