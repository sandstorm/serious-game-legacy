<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\EventStore;

use Neos\EventStore\Model\Event\CausationId;
use Neos\EventStore\Model\Event\CorrelationId;
use Neos\EventStore\Model\Event\EventId;
use Neos\EventStore\Model\Event\EventMetadata;

/**
 * If you want to publish an event with certain metadata, you can use DecoratedEvent
 *
 * @internal because no external entity can publish new events (only command handlers can)
 */
final readonly class DecoratedEvent
{
    private function __construct(
        public GameEventInterface $innerEvent,
        public ?EventId           $eventId,
        public ?EventMetadata     $eventMetadata,
        public ?CausationId       $causationId,
        public ?CorrelationId     $correlationId,
    ) {
    }

    /**
     * @param EventMetadata|array<string, mixed>|null $metadata
     */
    public static function create(
        DecoratedEvent|GameEventInterface $event,
        ?EventId                          $eventId = null,
        EventMetadata|array|null          $metadata = null,
        EventId|CausationId|null          $causationId = null,
        ?CorrelationId                    $correlationId = null,
    ): self {
        if ($event instanceof GameEventInterface) {
            $event = new self($event, null, null, null, null);
        }
        if ($causationId instanceof EventId) {
            $causationId = CausationId::fromString($causationId->value);
        }
        if (is_array($metadata)) {
            $metadata = EventMetadata::fromArray($metadata);
        }
        return new self($event->innerEvent, $eventId ?? $event->eventId, $metadata ?? $event->eventMetadata, $causationId ?? $event->causationId, $correlationId ?? $event->correlationId);
    }
}
