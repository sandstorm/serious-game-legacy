<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

use Domain\Definitions\Card\Dto\ResourceChanges;

readonly class Zeitsteine extends Resource implements \JsonSerializable
{
    public function __construct(public int $value)
    {
        parent::__construct(new ResourceId("Zeitsteine"));
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
