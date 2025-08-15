<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Modifier;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Traversable;

/**
 * @immutable
 * @implements \IteratorAggregate<\Domain\CoreGameLogic\Feature\Spielzug\Modifier\Modifier>
 */
readonly class ModifierCollection implements \IteratorAggregate
{
    /**
     * @param Modifier[] $modifiers
     */
    public function __construct(private array $modifiers)
    {
    }

    /**
     * @return Modifier[]
     */
    public function getModifiers(): array
    {
        return $this->modifiers;
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
     * @template T
     * @param T $value
     * @return T
     */
    public function modify(GameEvents $gameEvents, HookEnum $hookEnum, mixed $value): mixed
    {
        $returnValue = $value;
        foreach ($this->modifiers as $modifier) {
            assert($modifier instanceof Modifier);
            if ($modifier->canModify($hookEnum) && $modifier->isActive($gameEvents)) {
                $returnValue = $modifier->modify($value);
            }
        }
        return $returnValue;
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

    public function getActiveModifiersForHook(HookEnum $hookEnum, GameEvents $gameEvents): self
    {
        return $this->filter(fn (Modifier $modifier) => $modifier->canModify($hookEnum) && $modifier->isActive($gameEvents));
    }
}
