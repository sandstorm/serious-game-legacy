<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic;

use Domain\CoreGameLogic\CommandHandler\CommandBus;
use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Domain\CoreGameLogic\Dto\ValueObject\CurrentYear;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\Dto\ValueObject\Leitzins;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Initialization\Event\PlayerOrderingWasDefined;
use Domain\CoreGameLogic\Feature\Initialization\InitializationCommandHandler;
use Domain\CoreGameLogic\Feature\Jahreswechsel\Event\NewYearWasStarted;
use Domain\CoreGameLogic\Feature\Jahreswechsel\JahreswechselCommandHandler;
use Domain\CoreGameLogic\Feature\Spielzug\SpielzugCommandHandler;
use Neos\EventStore\Helper\InMemoryEventStore;
use Neos\EventStore\Model\EventStream\ExpectedVersion;

/**
 * Main implementation of core business logic
 *
 * This class implements the driving port {@see ForCoreGameLogic} and
 * coordinates the core domain operations. It:
 * - Contains the actual business logic
 * - Uses driven ports (e.g. ForLogging) to interact with external services
 * - Never directly depends on framework code
 *
 * @internal from the outside world, you'll always use the interface {@see ForCoreGameLogic}, except when constructing this application
 */
final class CoreGameLogicApp implements ForCoreGameLogic
{
    private CommandBus $commandBus;

    public static function createInMemoryForTesting(): ForCoreGameLogic
    {
        $eventStore = new GameEventStore(new InMemoryEventStore());
        return new self($eventStore);
    }

    public function __construct(
        private readonly GameEventStore $gameEventStore,
    )
    {
        $this->commandBus = new CommandBus(
            new InitializationCommandHandler(),
            new JahreswechselCommandHandler(),
            new SpielzugCommandHandler(),
        );
    }

    public function startGameIfNotStarted(GameId $gameId): void
    {
        // TODO: DO NOT HARDCODE PLAYERS HERE -> TODO: COMMAND...
        $p1 = new PlayerId('p1');
        $p2 = new PlayerId('p2');
        if (!$this->gameEventStore->hasGame($gameId)) {
            logger()->info('starting game', ['gameId' => $gameId->value]);
            $events = GameEventsToPersist::with(
                new PlayerOrderingWasDefined(
                    playerOrdering: [
                        $p1,
                        $p2,
                    ]
                ),
                new NewYearWasStarted(
                    newYear: new CurrentYear(1),
                    leitzins: new Leitzins(3)
                ),
            );
            $this->gameEventStore->commit($gameId, $events, ExpectedVersion::NO_STREAM());
        } else {
            logger()->debug('game already started', ['gameId' => $gameId->value]);
        }
    }

    public function getGameStream(GameId $gameId): GameEvents
    {
        [$gameStream,] = $this->gameEventStore->getGameStreamAndLastVersion($gameId);
        return $gameStream;
    }

    public function handle(GameId $gameId, CommandHandler\CommandInterface $command): void
    {
        [$gameStream, $version] = $this->gameEventStore->getGameStreamAndLastVersion($gameId);
        $eventsToPublish = $this->commandBus->handle($command, $gameStream);
        $this->gameEventStore->commit($gameId, $eventsToPublish, $version === null ? ExpectedVersion::NO_STREAM() : ExpectedVersion::fromVersion($version));
    }
}
