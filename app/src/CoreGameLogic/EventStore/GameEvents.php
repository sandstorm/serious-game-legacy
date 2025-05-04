<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\EventStore;

use Neos\EventStore\EventStoreInterface;

/**
 * A set of Game "domain events"
 *
 * For better type checking we ensure that this collection is never empty.
 * That is because {@see EventStoreInterface::commit()} will throw an exception if there are 0 events passed:
 *
 * > Writable events must contain at least one event
 *
 * We do not skip the case for 0 events to ensure each command always maps to a mutation.
 * Forgiving noop behaviour is not intended for this low level code.
 *
 * @implements \IteratorAggregate<GameEventInterface|DecoratedEvent>
 * @internal only used during event publishing (from within command handlers) - and their implementation is not API
 */
final readonly class GameEvents implements \IteratorAggregate, \Countable
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

    public static function with(GameEventInterface|DecoratedEvent $event): self
    {
        return new self($event);
    }

    public function withAppendedEvents(GameEvents $events): self
    {
        return new self(...$this->events, ...$events->events);
    }

    /**
     * @param non-empty-array<GameEventInterface|DecoratedEvent> $events
     * @return static
     */
    public static function fromArray(array $events): self
    {
        return new self(...$events);
    }

    public function getIterator(): \Traversable
    {
        yield from $this->events;
    }

    /**
     * @template T
     * @param \Closure(GameEventInterface|DecoratedEvent $event): T $callback
     * @return non-empty-list<T>
     */
    public function map(\Closure $callback): array
    {
        return array_map($callback, $this->events);
    }

    public function reduce(callable $callback, mixed $initial): mixed
    {
        return array_reduce($this->events, $callback, $initial);
    }

    public function count(): int
    {
        return count($this->events);
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @return T
     */
    public function findLast(string $className): object
    {
        $element = $this->findLastOrNull($className);
        if ($element === null) {
            return new \RuntimeException('No event of type '.$className.' found');
        }

        return $element;
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @return T|null
     */
    public function findLastOrNull(string $className): ?object
    {
        for ($i = count($this->events) - 1; $i >= 0; $i--) {
            if ($this->events[$i] instanceof $className) {
                return $this->events[$i];
            }
        }

        return null;
    }

    public function findAllOfType(string $className): self
    {
        return self::fromArray(array_filter($this->events, fn($event) => $event instanceof $className));
    }
}
