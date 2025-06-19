<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Moneysheet\Command\EnterLebenshaltungskostenForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\Command\EnterSteuernUndAbgabenForPlayer;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\LebenshaltungskostenForPlayerWereCorrected;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\LebenshaltungskostenForPlayerWereEntered;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\SteuernUndAbgabenForPlayerWereCorrected;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\SteuernUndAbgabenForPlayerWereEntered;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;

/**
 * @internal no public API, because commands are no extension points. ALWAYS USE {@see ForCoreGameLogic::handle()} to trigger commands.
 */
final readonly class MoneysheetCommandHandler implements CommandHandlerInterface
{
    public function canHandle(CommandInterface $command): bool
    {
        return $command instanceof EnterSteuernUndAbgabenForPlayer
            || $command instanceof EnterLebenshaltungskostenForPlayer;
    }

    public function handle(CommandInterface $command, GameEvents $gameEvents): GameEventsToPersist
    {
        /** @phpstan-ignore-next-line */
        return match ($command::class) {
            EnterSteuernUndAbgabenForPlayer::class => $this->handleEnterSteuernUndAbgabenForPlayer(
                $command, $gameEvents),
            EnterLebenshaltungskostenForPlayer::class => $this->handleEnterLebenshaltungskostenForPlayer(
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

        if ($previousTries >= 1 && !$expectedInput->equals($command->input)) {
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

        if ($previousTries >= 1 && !$expectedInput->equals($command->input)) {
            return $returnEvents->withAppendedEvents(
                new LebenshaltungskostenForPlayerWereCorrected($command->playerId, $expectedInput)
            );
        }

        return $returnEvents;
    }

}
