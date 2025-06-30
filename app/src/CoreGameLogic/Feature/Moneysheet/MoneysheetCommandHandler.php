<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Moneysheet\Command\CancelInsuranceForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\Command\ConcludeInsuranceForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\Command\EnterLebenshaltungskostenForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\Command\EnterSteuernUndAbgabenForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\Command\TakeOutALoanForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\InsuranceForPlayerWasCancelled;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\InsuranceForPlayerWasConcluded;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\LebenshaltungskostenForPlayerWereCorrected;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\LebenshaltungskostenForPlayerWereEntered;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\LoanWasTakenOutForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\SteuernUndAbgabenForPlayerWereCorrected;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\SteuernUndAbgabenForPlayerWereEntered;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Insurance\InsuranceFinder;
use Domain\Definitions\Insurance\ValueObject\InsuranceTypeEnum;

/**
 * @internal no public API, because commands are no extension points. ALWAYS USE {@see ForCoreGameLogic::handle()} to trigger commands.
 */
final readonly class MoneysheetCommandHandler implements CommandHandlerInterface
{
    public function canHandle(CommandInterface $command): bool
    {
        return $command instanceof EnterSteuernUndAbgabenForPlayer
            || $command instanceof EnterLebenshaltungskostenForPlayer
            || $command instanceof ConcludeInsuranceForPlayer
            || $command instanceof CancelInsuranceForPlayer
            || $command instanceof TakeOutALoanForPlayer;
    }

    public function handle(CommandInterface $command, GameEvents $gameEvents): GameEventsToPersist
    {
        /** @phpstan-ignore-next-line */
        return match ($command::class) {
            EnterSteuernUndAbgabenForPlayer::class => $this->handleEnterSteuernUndAbgabenForPlayer(
                $command, $gameEvents),
            EnterLebenshaltungskostenForPlayer::class => $this->handleEnterLebenshaltungskostenForPlayer(
                $command, $gameEvents),
            ConcludeInsuranceForPlayer::class => $this->handleConcludeInsuranceForPlayer(
                $command, $gameEvents),
            CancelInsuranceForPlayer::class => $this->handleCancelInsuranceForPlayer(
                $command, $gameEvents),
            TakeOutALoanForPlayer::class => $this->handleTakeOutALoanForPlayer(
                $command, $gameEvents),
        };
    }

    private function handleEnterSteuernUndAbgabenForPlayer(
        EnterSteuernUndAbgabenForPlayer $command,
        GameEvents $gameEvents
    ): GameEventsToPersist {
        $expectedInput = MoneySheetState::calculateSteuernUndAbgabenForPlayer($gameEvents, $command->playerId);
        $previousTries = MoneySheetState::getNumberOfTriesForSteuernUndAbgabenInput($gameEvents, $command->playerId);

        $returnEvents = GameEventsToPersist::with(
            new SteuernUndAbgabenForPlayerWereEntered(
                playerId: $command->playerId,
                playerInput: $command->input,
                expectedInput: $expectedInput,
                wasInputCorrect: $expectedInput->equals($command->input),
            )
        );

        if ($previousTries >= Configuration::MAX_NUMBER_OF_TRIES_PER_INPUT - 1 && !$expectedInput->equals($command->input)) {
            return $returnEvents->withAppendedEvents(
                new SteuernUndAbgabenForPlayerWereCorrected($command->playerId, $expectedInput)
            );
        }

        return $returnEvents;
    }

    private function handleEnterLebenshaltungskostenForPlayer(
        EnterLebenshaltungskostenForPlayer $command,
        GameEvents $gameEvents
    ): GameEventsToPersist {
        $expectedInput = MoneySheetState::calculateLebenshaltungskostenForPlayer($gameEvents, $command->playerId);
        $previousTries = MoneySheetState::getNumberOfTriesForLebenshaltungskostenInput($gameEvents, $command->playerId);

        $returnEvents = GameEventsToPersist::with(
            new LebenshaltungskostenForPlayerWereEntered(
                playerId: $command->playerId,
                playerInput: $command->input,
                expectedInput: $expectedInput,
                wasInputCorrect: $expectedInput->equals($command->input),
            )
        );

        if ($previousTries >= Configuration::MAX_NUMBER_OF_TRIES_PER_INPUT - 1 && !$expectedInput->equals($command->input)) {
            return $returnEvents->withAppendedEvents(
                new LebenshaltungskostenForPlayerWereCorrected($command->playerId, $expectedInput)
            );
        }

        return $returnEvents;
    }

    private function handleConcludeInsuranceForPlayer(
        ConcludeInsuranceForPlayer $command,
        GameEvents                 $gameEvents
    ): GameEventsToPersist {
        $hasInsurance = MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $command->playerId, $command->insuranceId);

        if ($hasInsurance) {
            throw new \RuntimeException("Cannot conclude insurance that was already concluded.");
        }

        return GameEventsToPersist::with(
            new InsuranceForPlayerWasConcluded(
                playerId: $command->playerId,
                insuranceId: $command->insuranceId,
            )
        );
    }

    private function handleCancelInsuranceForPlayer(
        CancelInsuranceForPlayer $command,
        GameEvents $gameEvents
    ): GameEventsToPersist {
        $hasInsurance = MoneySheetState::doesPlayerHaveThisInsurance($gameEvents, $command->playerId, $command->insuranceId);

        if (!$hasInsurance) {
            throw new \RuntimeException("Cannot cancel insurance that was not concluded.");
        }

        return GameEventsToPersist::with(
            new InsuranceForPlayerWasCancelled(
                playerId: $command->playerId,
                insuranceId: $command->insuranceId,
            )
        );
    }

    private function handleTakeOutALoanForPlayer(TakeOutALoanForPlayer $command, GameEvents $gameEvents): GameEventsToPersist
    {
        // player needs a job to take out a loan
        $playerHasJob = PlayerState::getJobForPlayer($gameEvents, $command->playerId);
        if ($playerHasJob === null) {
            throw new \RuntimeException("Cannot take out a loan without a job.");
        }

        // player needs BU insurance to take out a loan
        $insurance = InsuranceFinder::getInstance()->findInsuranceByType(InsuranceTypeEnum::BERUFSUNFAEHIGKEITSVERSICHERUNG);
        $hasInsurance = MoneySheetState::doesPlayerHaveThisInsurance(
            $gameEvents,
            $command->playerId,
            $insurance->id
        );

        if (!$hasInsurance) {
            throw new \RuntimeException("Cannot take out a loan without Berufsunfähigkeitsversicherung.");
        }

        return GameEventsToPersist::with(
            new LoanWasTakenOutForPlayer(
                playerId: $command->playerId,
                intendedUse: $command->intendedUse,
                loanAmount: $command->loanAmount,
                repaymentAmount: $command->repaymentAmount,
                repaymentPerKonjunkturphase: $command->repaymentPerKonjunkturphase,
            )
        );
    }
}
