<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

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
