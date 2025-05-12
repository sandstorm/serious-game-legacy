<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

use Traversable;

/**
 * @immutable
 * @implements \IteratorAggregate<\Domain\CoreGameLogic\Dto\ValueObject\ResourceChange>
 */
readonly class ResourceChangeCollection implements \IteratorAggregate
{
    /**
     * @param ResourceChange[] $resourceChanges
     */
    public function __construct(private array $resourceChanges)
    {
    }


    public function withAdditional(self $resourceChangeCollection): self
    {
        return new self(array_merge($this->resourceChanges, $resourceChangeCollection->resourceChanges));
    }

    /**
     * @return Traversable<ResourceChange>
     */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->resourceChanges);
    }

    /**
     * @param \Closure(ResourceChange $resourceChange): bool $callback
     * @return self
     */
    public function filter(\Closure $callback): self
    {
        return new self(array_filter($this->resourceChanges, $callback));
    }

    /**
     * @param \Closure(ResourceChange $carry, ResourceChange $modifier): ResourceChange $callback
     * @param mixed $initial
     * @return mixed
     */
    public function reduce(callable $callback, mixed $initial): mixed
    {
        return array_reduce($this->resourceChanges, $callback, $initial);
    }
}
