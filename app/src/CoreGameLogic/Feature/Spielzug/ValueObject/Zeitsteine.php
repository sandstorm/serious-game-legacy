<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\ValueObject;

use Domain\CoreGameLogic\Dto\ValueObject\ResourceId;
use Domain\Definitions\Card\Dto\ResourceChanges;

readonly class Zeitsteine implements \JsonSerializable
{
    public function __construct(public int $value)
    {
    }

    public function __toString(): string
    {
        return '[Zeitsteine: '.$this->value.']';
    }

    public function jsonSerialize(): int
    {
        return $this->value;
    }

    public function withChange(ResourceChanges $resourceChangeChange): self
    {
        return new self($this->value + $resourceChangeChange->zeitsteineChange);
    }
}
