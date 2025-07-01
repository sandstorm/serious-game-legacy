
<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Moneysheet\Command\CancelInsuranceForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\Command\ConcludeInsuranceForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\Command\TakeOutALoanForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\InsuranceForPlayerWasCancelled;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\InsuranceForPlayerWasConcluded;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\LoanWasTakenOutForPlayer;
use Domain\Definitions\Configuration\Configuration;

/**
 * @internal no public API, because commands are no extension points. ALWAYS USE {@see ForCoreGameLogic::handle()} to trigger commands.
 */
final readonly class MoneysheetCommandHandler implements CommandHandlerInterface
{
    public function canHandle(CommandInterface $command): bool
    {
        return $command instanceof ConcludeInsuranceForPlayer
            || $command instanceof CancelInsuranceForPlayer
            || $command instanceof TakeOutALoanForPlayer;
    }

    public function handle(CommandInterface $command, GameEvents $gameEvents): GameEventsToPersist
    {
        /** @phpstan-ignore-next-line */
        return match ($command::class) {
            ConcludeInsuranceForPlayer::class => $this->handleConcludeInsuranceForPlayer(
                $command, $gameEvents),
            CancelInsuranceForPlayer::class => $this->handleCancelInsuranceForPlayer(
                $command, $gameEvents),
            TakeOutALoanForPlayer::class => $this->handleTakeOutALoanForPlayer(
                $command, $gameEvents),
        };
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
        return GameEventsToPersist::with(
            new LoanWasTakenOutForPlayer(
                playerId: $command->playerId,
                intendedUse: $command->intendedUse,
                loanAmount: $command->loanAmount,
                totalRepayment: $command->totalRepayment,
                repaymentPerKonjunkturphase: $command->repaymentPerKonjunkturphase,
            )
        );
    }
}
