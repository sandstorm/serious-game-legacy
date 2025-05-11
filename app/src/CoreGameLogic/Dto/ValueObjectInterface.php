<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto;

/**
 * All value objects should be marked with this interface, to
 * convert them transparently in Livewire components:
 *
 * - They need to expose their property with a public property "value"
 * - They need to have static {@see self::fromString()} factory method.
 */
interface ValueObjectInterface
{
    public static function fromString(string $value): self;
}
