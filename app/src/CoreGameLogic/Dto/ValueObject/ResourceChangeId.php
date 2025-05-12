<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

final readonly class ResourceChangeId implements \JsonSerializable
{
    public function __construct(public string $value)
    {
    }

    public function __toString(): string
    {
        return '[ResourceChangeId: '.$this->value.']';
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
