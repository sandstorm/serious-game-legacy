<?php

namespace Domain\CoreGameLogic\Dto;

/**
 * All value objects should be marked with this interface, to
 * convert them transparently in Livewire components:
 *
 * - They need to expose their property with a public property "value"
 * - They need to have a public, 1 element constructor.
 */
interface ValueObjectInterface
{
}
