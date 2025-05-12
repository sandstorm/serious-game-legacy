<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\Dto\ValueObject\CurrentYear;
use Domain\CoreGameLogic\Dto\ValueObject\Leitzins;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Initialization\Command\DefinePlayerOrdering;
use Domain\CoreGameLogic\Feature\Initialization\Command\InitPlayerGuthaben;
use Domain\CoreGameLogic\Feature\Initialization\Command\LebenszielAuswaehlen;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartGame;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Initialization\Event\GuthabenInitialized;
use Domain\CoreGameLogic\Feature\Initialization\Event\LebenszielChosen;
use Domain\CoreGameLogic\Feature\Initialization\Event\NameForPlayerWasSet;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Initialization\Event\PreGameStarted;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Initialization\State\LebenszielAccessor;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Jahreswechsel\Event\NewYearWasStarted;

/**
 * @internal no public API, because commands are no extension points. ALWAYS USE {@see ForCoreGameLogic::handle()} to trigger commands.
 */
final readonly class InitializationCommandHandler implements CommandHandlerInterface
{
    public function canHandle(CommandInterface $command): bool
    {
        return $command instanceof DefinePlayerOrdering
            || $command instanceof LebenszielAuswaehlen
            || $command instanceof StartGame
            || $command instanceof StartPreGame
            || $command instanceof SetNameForPlayer
            || $command instanceof InitPlayerGuthaben;
    }

    public function handle(CommandInterface $command, GameEvents $gameState): GameEventsToPersist
    {
        /** @phpstan-ignore-next-line */
        return match ($command::class) {
            DefinePlayerOrdering::class => $this->handleDefinePlayerOrdering($command, $gameState),
            LebenszielAuswaehlen::class => $this->handleLebenszielAuswaehlen($command, $gameState),
            StartGame::class => $this->handleStartGame($command, $gameState),

            StartPreGame::class => $this->handleStartPreGame($command, $gameState),
            SetNameForPlayer::class => $this->handleSetNameForPlayer($command, $gameState),
            InitPlayerGuthaben::class => $this->handleInitPlayerGuthaben($command, $gameState),
        };
    }

    private function handleDefinePlayerOrdering(DefinePlayerOrdering $command, GameEvents $gameState): GameEventsToPersist
    {
        return GameEventsToPersist::with(
            new GameWasStarted(playerOrdering: $command->playerOrdering),
        );
    }

    private function handleLebenszielAuswaehlen(LebenszielAuswaehlen $command, GameEvents $gameState): GameEventsToPersist
    {
        $lebensziel = PreGameState::lebenszielForPlayerOrNull($gameState, $command->playerId);
        if ($lebensziel !== null) {
            throw new \RuntimeException('Player has already selected a Lebensziel', 1746713490);
        }

        return GameEventsToPersist::with(
            new LebenszielChosen(
                playerId: $command->playerId,
                lebensziel: $command->lebensziel,
            )
        );
    }

    private function handleStartGame(StartGame $command, GameEvents $gameState): GameEventsToPersist
    {

        if (!PreGameState::isReadyForGame($gameState)) {
            throw new \RuntimeException('not ready for game', 1746713490);
        }

        if (GamePhaseState::isInGamePhase($gameState)) {
            throw new \RuntimeException('game already started', 1746713490);
        }

        return GameEventsToPersist::with(
            new GameWasStarted(
                playerOrdering: $command->playerOrdering
            ),
            // TODO: this cannot be hardcoded here :) Maybe delegate to the other command handler??
            new NewYearWasStarted(
                newYear: new CurrentYear(1),
                leitzins: new Leitzins(3)
            ),
        );
    }

    private function handleStartPreGame(StartPreGame $command, GameEvents $gameState): GameEventsToPersist
    {
        if (count($gameState) > 0) {
            throw new \RuntimeException('Game has already started', 1746713490);
        }

        if (count($command->fixedPlayerIdsForTesting) > 0) {
            return GameEventsToPersist::with(
                new PreGameStarted(
                    playerIds: $command->fixedPlayerIdsForTesting
                ),
            );
        }

        // Generate random, short PlayerIds
        $playerIds = [];
        for ($i = 0; $i < $command->numberOfPlayers; $i++) {
            $playerIds[] = PlayerId::random();
        }

        return GameEventsToPersist::with(
            new PreGameStarted(
                playerIds: $playerIds
            ),
        );
    }

    public function handleSetNameForPlayer(SetNameForPlayer $command, GameEvents $gameState): GameEventsToPersist
    {
        if (!in_array($command->playerId, PreGameState::playerIds($gameState), true)) {
            throw new \RuntimeException('wrong PlayerId given');
        }

        return GameEventsToPersist::with(
            new NameForPlayerWasSet(
                playerId: $command->playerId,
                name: $command->name
            ),
        );
    }

    public function handleInitPlayerGuthaben(InitPlayerGuthaben $command, GameEvents $gameState): GameEventsToPersist
    {
        $eventsToPersist = [];
        foreach (PreGameState::playerIds($gameState) as $playerId) {
            $eventsToPersist[] = new GuthabenInitialized(
                $playerId,
                $command->initialGuthaben,
            );
        }
        return GameEventsToPersist::with(...$eventsToPersist);
    }
}
