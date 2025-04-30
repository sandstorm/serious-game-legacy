<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto;

use Wwwision\Types\Attributes\Description;
use Wwwision\Types\Attributes\StringBased;

#[Description('The full name of a contact, e.g. "John Doe"')]
#[StringBased(minLength: 1, maxLength: 200)]
readonly class UserName
{
    private function __construct(public string $value)
    {
    }

    public function __toString(): string
    {
        return '[UserName: '.$this->value.']';
    }
}
