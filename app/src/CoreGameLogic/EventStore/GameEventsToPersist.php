<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\EventStore;

/**
 * A set of Game "domain events" which should be appended to the event stream.
 *
 * This is only used on the WRITE SIDE of the event stream.
 *
 * @implements \IteratorAggregate<GameEventInterface|DecoratedEvent>
 * @internal only used during event publishing (from within command handlers) - and their implementation is not API
 */
final readonly class GameEventsToPersist implements \IteratorAggregate, \Countable
{
    /**
     * @var non-empty-array<GameEventInterface|DecoratedEvent>
     */
    public array $events;

    private function __construct(GameEventInterface|DecoratedEvent ...$events)
    {
        /** @var non-empty-array<GameEventInterface|DecoratedEvent> $events */
        $this->events = $events;
    }

    public static function with(GameEventInterface|DecoratedEvent ...$events): self
    {
        return new self(...$events);
    }

    public static function empty(): self
    {
        return new self();
    }

    public function withAppendedEvents(GameEventInterface|DecoratedEvent ...$events): self
    {
        return new self(...$this->events, ...$events);
    }

    public function getIterator(): \Traversable
    {
        yield from $this->events;
    }

    /**
     * @template T
     * @param \Closure(GameEventInterface|DecoratedEvent $event): T $callback
     * @return non-empty-array<T>
     */
    public function map(\Closure $callback): array
    {
        return array_map($callback, $this->events);
    }

    public function count(): int
    {
        return count($this->events);
    }
}
