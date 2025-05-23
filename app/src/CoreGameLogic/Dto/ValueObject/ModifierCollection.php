<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

use Domain\CoreGameLogic\Dto\Aktion\Aktion;
use Traversable;

/**
 * @immutable
 * @implements \IteratorAggregate<\Domain\CoreGameLogic\Dto\ValueObject\Modifier>
 */
readonly class ModifierCollection implements \IteratorAggregate
{
    /**
     * @param Modifier[] $modifiers
     */
    public function __construct(private array $modifiers)
    {
    }


    public function withAdditional(self $modifierCollection): self
    {
        return new self(array_merge($this->modifiers, $modifierCollection->modifiers));
    }

    /**
     * @return Traversable<Modifier>
     */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->modifiers);
    }

    /**
     * @param Aktion[] $applicableAktionen
     * @return Aktion[]
     */
    public function applyToAvailableAktionen(array $applicableAktionen): array
    {
        foreach ($this->modifiers as $modifier) {
            assert($modifier instanceof Modifier);
            $applicableAktionen = $modifier->applyToAvailableAktionen($applicableAktionen);
        }
        return $applicableAktionen;
    }

    /**
     * @param \Closure(Modifier $modifier): bool $callback
     * @return self
     */
    public function filter(\Closure $callback): self
    {
        return new self(array_filter($this->modifiers, $callback));
    }

    /**
     * @template T
     * @param \Closure(T $carry, Modifier $modifier): T $callback
     * @param mixed $initial
     * @return mixed
     */
    public function reduce(callable $callback, mixed $initial): mixed
    {
        return array_reduce($this->modifiers, $callback, $initial);
    }
}
