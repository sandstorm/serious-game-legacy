<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization;

use Domain\CoreGameLogic\CommandHandler\CommandHandlerInterface;
use Domain\CoreGameLogic\CommandHandler\CommandInterface;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Initialization\Command\DefinePlayerOrdering;
use Domain\CoreGameLogic\Feature\Initialization\Command\SelectPlayerColor;
use Domain\CoreGameLogic\Feature\Initialization\Command\SelectLebensziel;
use Domain\CoreGameLogic\Feature\Initialization\Command\SetNameForPlayer;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartGame;
use Domain\CoreGameLogic\Feature\Initialization\Command\StartPreGame;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Initialization\Event\LebenszielWasSelected;
use Domain\CoreGameLogic\Feature\Initialization\Event\NameForPlayerWasSet;
use Domain\CoreGameLogic\Feature\Initialization\Event\PlayerColorWasSelected;
use Domain\CoreGameLogic\Feature\Initialization\Event\PreGameStarted;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Initialization\ValueObject\PlayerColor;
use Domain\CoreGameLogic\Feature\Initialization\ValueObject\PlayerColors;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Lebensziel\LebenszielFinder;

/**
 * @internal no public API, because commands are no extension points. ALWAYS USE {@see ForCoreGameLogic::handle()} to trigger commands.
 */
final readonly class InitializationCommandHandler implements CommandHandlerInterface
{
    public function canHandle(CommandInterface $command): bool
    {
        return $command instanceof DefinePlayerOrdering
            || $command instanceof SelectLebensziel
            || $command instanceof StartGame
            || $command instanceof StartPreGame
            || $command instanceof SetNameForPlayer
            || $command instanceof SelectPlayerColor;
    }

    public function handle(CommandInterface $command, GameEvents $gameEvents): GameEventsToPersist
    {
        /** @phpstan-ignore-next-line */
        return match ($command::class) {
            DefinePlayerOrdering::class => $this->handleDefinePlayerOrdering($command, $gameEvents),
            SelectLebensziel::class => $this->handleSelectLebensziel($command, $gameEvents),
            StartGame::class => $this->handleStartGame($command, $gameEvents),
            SelectPlayerColor::class => $this->handleSelectColor($command, $gameEvents),

            StartPreGame::class => $this->handleStartPreGame($command, $gameEvents),
            SetNameForPlayer::class => $this->handleSetNameForPlayer($command, $gameEvents),
        };
    }

    private function handleDefinePlayerOrdering(DefinePlayerOrdering $command, GameEvents $gameState): GameEventsToPersist
    {
        return GameEventsToPersist::with(
            new GameWasStarted(playerOrdering: $command->playerOrdering),
        );
    }

    private function handleSelectLebensziel(SelectLebensziel $command, GameEvents $gameState): GameEventsToPersist
    {
        $lebensziel = PreGameState::lebenszielForPlayerOrNull($gameState, $command->playerId);
        if ($lebensziel !== null) {
            throw new \RuntimeException('Player has already selected a Lebensziel', 1746713490);
        }

        $lebensziel = LebenszielFinder::findLebenszielById($command->lebensziel);

        return GameEventsToPersist::with(
            new LebenszielWasSelected(
                playerId: $command->playerId,
                lebensziel: $lebensziel,
            )
        );
    }

    private function handleSelectColor(SelectPlayerColor $command, GameEvents $gameState): GameEventsToPersist
    {
        if (GamePhaseState::isInGamePhase($gameState)) {
            throw new \RuntimeException('game already started', 1746713495);
        }

        $usedColors = $gameState->findAllOfType(PlayerColorWasSelected::class);
        $newColor = array_values(array_filter(PlayerColors::asArray(), function ($color) use ($usedColors) {
            /** @var PlayerColorWasSelected $event **/
            foreach ($usedColors as $event) {
                if ($event->playerColor->value === $color) {
                    return false; // color is already used
                }
            }
            return true; // color is available
        }));

        return GameEventsToPersist::with(
            new PlayerColorWasSelected(
                playerId: $command->playerId,
                playerColor: $command->playerColor !== null ? $command->playerColor : new PlayerColor($newColor[0]),
            ),
        );
    }

    private function handleStartGame(StartGame $command, GameEvents $gameState): GameEventsToPersist
    {

        if (!PreGameState::isReadyForGame($gameState)) {
            throw new \RuntimeException('not ready for game', 1746713491);
        }

        if (GamePhaseState::isInGamePhase($gameState)) {
            throw new \RuntimeException('game already started', 1746713492);
        }

        return GameEventsToPersist::with(
            new GameWasStarted(
                playerOrdering: PreGameState::playerIds($gameState),
            ),
        );
    }

    private function handleStartPreGame(StartPreGame $command, GameEvents $gameState): GameEventsToPersist
    {
        if (count($gameState) > 0) {
            throw new \RuntimeException('Game has already started', 1746713493);
        }

        if (count($command->fixedPlayerIdsForTesting) > 0) {
            return GameEventsToPersist::with(
                new PreGameStarted(
                    playerIds: $command->fixedPlayerIdsForTesting,
                    resourceChanges: new ResourceChanges(
                        guthabenChange: new MoneyAmount(Configuration::STARTKAPITAL_VALUE),
                    ),
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
                playerIds: $playerIds,
                resourceChanges: new ResourceChanges(
                    guthabenChange: new MoneyAmount(Configuration::STARTKAPITAL_VALUE),
                ),
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
}
