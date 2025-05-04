<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

readonly class CurrentYear
{
    public function __construct(public int $value)
    {
    }

    public function __toString(): string
    {
        return '[Jahr: '.$this->value.']';
    }
}
