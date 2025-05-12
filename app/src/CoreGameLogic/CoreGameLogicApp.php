<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic;

use Domain\CoreGameLogic\CommandHandler\CommandBus;
use Domain\CoreGameLogic\DrivingPorts\ForCoreGameLogic;
use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\InitializationCommandHandler;
use Domain\CoreGameLogic\Feature\Pile\PileCommandHandler;
use Domain\CoreGameLogic\Feature\KonjunkturzyklusWechseln\KonjunkturzykluswechselCommandHandler;
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
    ) {
        $this->commandBus = new CommandBus(
            new InitializationCommandHandler(),
            new KonjunkturzykluswechselCommandHandler(),
            new SpielzugCommandHandler(),
            new PileCommandHandler(),
        );
    }

    public function hasGame(GameId $gameId): bool
    {
        return $this->gameEventStore->hasGame($gameId);
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
