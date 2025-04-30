<?php

namespace Domain\CoreGameLogic\Dto\ValueObject;

use Traversable;

/**
 * @immutable
 */
readonly class ModifierCollection implements \IteratorAggregate
{
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

    public function applyToAvailableAktionen(array $applicableAktionen)
    {
        foreach ($this->modifiers as $modifier) {
            assert($modifier instanceof Modifier);
            $applicableAktionen = $modifier->applyToAvailableAktionen($applicableAktionen);
        }
        return $applicableAktionen;
    }
}
