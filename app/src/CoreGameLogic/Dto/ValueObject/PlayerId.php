<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

use Wwwision\Types\Attributes\Description;
use Wwwision\Types\Attributes\StringBased;

readonly class PlayerId
{
    public function __construct(public string $value)
    {
    }

    public function __toString(): string
    {
        return '[Player: '.$this->value.']';
    }

    public function equals(PlayerId $other):bool
    {
        return $this->value === $other->value;
    }
}
