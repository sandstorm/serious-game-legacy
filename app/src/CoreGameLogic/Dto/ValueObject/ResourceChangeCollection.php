<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

use Traversable;

/**
 * @immutable
 * @implements \IteratorAggregate<\Domain\CoreGameLogic\Dto\ValueObject\ResourceChanges>
 */
readonly class ResourceChangeCollection implements \IteratorAggregate
{
    /**
     * @param ResourceChanges[] $resourceChanges
     */
    public function __construct(private array $resourceChanges)
    {
    }

    public static function fromArray(array $values): self
    {
        $resourceChanges = [];
        foreach ($values as $value) {
            $resourceChanges = ResourceChanges::fromArray($values['resourceChanges']);
        }
        return new self(resourceChanges: $resourceChanges);
    }


    public function withAdditional(self $resourceChangeCollection): self
    {
        return new self(array_merge($this->resourceChanges, $resourceChangeCollection->resourceChanges));
    }

    /**
     * @return Traversable<ResourceChanges>
     */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->resourceChanges);
    }

    /**
     * @param \Closure(ResourceChanges $resourceChange): bool $callback
     * @return self
     */
    public function filter(\Closure $callback): self
    {
        return new self(array_filter($this->resourceChanges, $callback));
    }

    /**
     * @param \Closure(ResourceChanges $carry, ResourceChanges $modifier): ResourceChanges $callback
     * @param mixed $initial
     * @return mixed
     */
    public function reduce(callable $callback, mixed $initial): mixed
    {
        return array_reduce($this->resourceChanges, $callback, $initial);
    }

    public function jsonSerialize(): array
    {
        $resourceChanges = [];
        foreach ($this->resourceChanges as $resourceChange) {
            $resourceChanges[] = $resourceChange->jsonSerialize();
        }
        return [
            'resourceChanges' => $resourceChanges,
        ];
    }
}
