<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\ValueObject;

readonly class Leitzins implements \JsonSerializable
{
    /**
     * @param float $value in %
     */
    public function __construct(public float $value)
    {
    }

    public function __toString(): string
    {
        return '[Leitzins: '.$this->value.']';
    }

    public function jsonSerialize(): float
    {
        return $this->value;
    }
}
