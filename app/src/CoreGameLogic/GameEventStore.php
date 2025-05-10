<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic;

use Domain\CoreGameLogic\Dto\ValueObject\GameId;
use Domain\CoreGameLogic\EventStore\EventNormalizer;
use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Neos\EventStore\EventStoreInterface;
use Neos\EventStore\Model\Event\Version;
use Neos\EventStore\Model\Events;
use Neos\EventStore\Model\EventStream\ExpectedVersion;

/**
 * @internal from the outside world, you'll always use {@see CoreGameLogicApp})
 */
final readonly class GameEventStore
{
    private EventNormalizer $eventNormalizer;
    public function __construct(
        private EventStoreInterface $eventStore,
    ) {
        $this->eventNormalizer = EventNormalizer::create();
    }

    public function hasGame(GameId $gameId): bool
    {
        foreach ($this->eventStore->load($gameId->streamName()) as $event) {
            // we found at least one event; so the game exists.
            return true;
        }

        // we did not find any events for this game, so it does not exist
        return false;
    }

    /**
     * @param GameId $gameId
     * @return array{0: GameEvents, 1: Version|null}
     */
    public function getGameStreamAndLastVersion(GameId $gameId): array
    {
        $gameEvents = [];
        $version = null;
        foreach ($this->eventStore->load($gameId->streamName()) as $eventEnvelope) {
            $gameEvents[] = $this->eventNormalizer->denormalize($eventEnvelope->event);
            $version = $eventEnvelope->version;
        }
        return [GameEvents::fromArray($gameEvents), $version];
    }

    public function commit(GameId $gameId, GameEventsToPersist $events, ExpectedVersion $expectedVersion): void
    {
        $this->eventStore->commit($gameId->streamName(), $this->enrichAndNormalizeEvents($events), $expectedVersion);
    }

    private function enrichAndNormalizeEvents(GameEventsToPersist $events): Events
    {
        // TODO: $initiatingUserId = $this->authProvider->getAuthenticatedUserId() ?? UserId::forSystemUser();
        // TODO: $initiatingTimestamp = $this->clock->now();

        return Events::fromArray($events->map(function (EventStore\DecoratedEvent|GameEventInterface $event) {
            return $this->eventNormalizer->normalize($event);
        }));
    }

}
