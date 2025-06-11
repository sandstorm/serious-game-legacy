<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Moneysheet\Command\EnterSteuernUndAbgabenForPlayer;
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
        return $command instanceof EnterSteuernUndAbgabenForPlayer;
    }

    public function handle(CommandInterface $command, GameEvents $gameState): GameEventsToPersist
    {
        /** @phpstan-ignore-next-line */
        return match ($command::class) {
            EnterSteuernUndAbgabenForPlayer::class => $this->handleEnterSteuernUndAbgabenForPlayer($command,
                $gameState),
        };
    }

    private function handleEnterSteuernUndAbgabenForPlayer(
        EnterSteuernUndAbgabenForPlayer $command,
        GameEvents $gameState
    ): GameEventsToPersist {
        $expectedInput = MoneySheetState::calculateSteuernUndAbgabenForPlayer($gameState, $command->playerId);

        $previousTries = MoneySheetState::getNumberOfTriesForSteuernUndAbgabenInput($gameState, $command->playerId);

        $returnEvents = GameEventsToPersist::with(
            new SteuernUndAbgabenForPlayerWereEntered(
                playerId: $command->playerId,
                playerInput: $command->input,
                expectedInput: $expectedInput,
                wasInputCorrect: $expectedInput === $command->input,
            )
        );

        if ($previousTries >= 1 && $expectedInput !== $command->input) {
            return $returnEvents->withAppendedEvents(
                new SteuernUndAbgabenForPlayerWereCorrected($command->playerId, $expectedInput)
            );
        }

        return $returnEvents;
    }

}
