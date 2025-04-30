<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

readonly class CardId
{
    public function __construct(public string $value)
    {
    }

    public function __toString(): string
    {
        return '[CardId: '.$this->value.']';
    }
}
