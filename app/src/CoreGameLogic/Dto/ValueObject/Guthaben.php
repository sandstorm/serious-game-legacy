<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

readonly class Guthaben extends Resource implements \JsonSerializable
{
    public function __construct(public int $value)
    {
        parent::__construct(new ResourceId("Guthaben"));
    }

    public function __toString(): string
    {
        return '[Guthaben: '.$this->value.']';
    }

    public function jsonSerialize(): int
    {
        return $this->value;
    }

    public function withChange(GuthabenChange $guthabenChange): self
    {
        return new self($this->value + $guthabenChange->value);
    }
}
