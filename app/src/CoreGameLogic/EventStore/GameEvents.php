<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\EventStore;

/**
 * A set of Game "domain events", as loaded from the Event Store.
 *
 * This is only used on the READ SIDE of the event stream.
 *
 * @implements \IteratorAggregate<GameEventInterface>
 * @internal only used during event publishing (from within command handlers) - and their implementation is not API
 */
final readonly class GameEvents implements \IteratorAggregate, \Countable
{
    /**
     * @var non-empty-array<GameEventInterface>
     */
    public array $events;

    private function __construct(GameEventInterface ...$events)
    {
        /** @var non-empty-array<GameEventInterface> $events */
        $this->events = $events;
    }

    public static function with(GameEventInterface $event): self
    {
        return new self($event);
    }

    /**
     * @param array<GameEventInterface> $events
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
     * @param \Closure(GameEventInterface $event): T $callback
     * @return non-empty-array<T>
     */
    public function map(\Closure $callback): array
    {
        return array_map($callback, $this->events);
    }

    public function reduce(callable $callback, mixed $initial): mixed
    {
        return array_reduce($this->events, $callback, $initial);
    }

    public function filter(callable $callback): mixed
    {
        return array_values(array_filter($this->events, $callback));
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
            throw new \RuntimeException('No event of type ' . $className . ' found');
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
        // @phpstan-ignore return.type
        return $this->findLastOrNullWhere(fn($event) => $event instanceof $className);
    }

    /**
     * @param callable(GameEventInterface):bool $filter
     * @return GameEventInterface|null
     */
    public function findLastOrNullWhere(callable $filter): ?object
    {
        for ($i = count($this->events) - 1; $i >= 0; $i--) {
            if ($filter($this->events[$i])) {
                return $this->events[$i];
            }
        }

        return null;
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @return T
     */
    public function findFirst(string $className): object
    {
        $element = $this->findFirstOrNull($className);
        if ($element === null) {
            throw new \RuntimeException('No event of type ' . $className . ' found');
        }

        return $element;
    }


    /**
     * @template T of object
     * @param class-string<T> $className
     * @return T|null
     */
    public function findFirstOrNull(string $className): ?object
    {
        for ($i = 0; $i <= count($this->events) - 1; $i++) {
            if ($this->events[$i] instanceof $className) {
                return $this->events[$i];
            }
        }

        return null;
    }

    /**
     * @template T
     * @param class-string<T> $className
     * @return GameEvents
     */
    public function findAllOfType(string $className): self
    {
        return self::fromArray(array_filter($this->events, fn($event) => $event instanceof $className));
    }

    /**
     * Returns all events that happened after the last event of the given type.
     * Throws an exception if no instance of the given event is found in the event stream.
     * @template T
     * @param class-string<T> $className
     * @return GameEvents
     */
    public function findAllAfterLastOfType(string $className): self
    {
        $indexOfLast = $this->findIndexOfLastOrNull($className);

        if ($indexOfLast === null) {
            throw new \RuntimeException('No element of type ' . $className . ' found', 1747412985);
        }

        if ($this->count() === $indexOfLast + 1) {
            return self::fromArray([]);
        }

        return self::fromArray(array_slice($this->events, $indexOfLast + 1));
    }

    private function findIndexOfLastOrNull(string $className): ?int
    {
        for ($i = count($this->events) - 1; $i >= 0; $i--) {
            if ($this->events[$i] instanceof $className) {
                return $i;
            }
        }

        return null;
    }

}
