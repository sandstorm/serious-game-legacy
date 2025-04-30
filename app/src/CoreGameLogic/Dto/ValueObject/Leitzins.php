<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

use Wwwision\Types\Attributes\Description;
use Wwwision\Types\Attributes\StringBased;

readonly class Leitzins
{
    /**
     * @param int $value in %
     */
    public function __construct(public int $value)
    {
    }

    public function __toString(): string
    {
        return '[Leitzins: '.$this->value.']';
    }
}
