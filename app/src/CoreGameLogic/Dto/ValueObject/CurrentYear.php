<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

use Wwwision\Types\Attributes\Description;
use Wwwision\Types\Attributes\StringBased;

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
