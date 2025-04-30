<?php

namespace Domain\CoreGameLogic\Dto\Event;

/**
 * @immutable
 *
 * TODO: GameStream??
 */
readonly class EventStream
{
    public function __construct(private array $events)
    {
    }

    public static function fromEvents(array $events)
    {
        return new self($events);
    }

    public function map(callable $callback): self
    {
        return new self(array_map($callback, $this->events));
    }

    public function reduce(callable $callback, mixed $initial): mixed
    {
        return array_reduce($this->events, $callback, $initial);
    }

    public function withAdditionalEvents(array $additionalEvents): self
    {
        return new self(array_merge($this->events, $additionalEvents));
    }

    public function findAllOfType(string $className): self
    {
        return new self(array_filter($this->events, fn($event) => $event instanceof $className));
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
}
