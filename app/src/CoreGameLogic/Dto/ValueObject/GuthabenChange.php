<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

readonly class GuthabenChange extends ResourceChange implements \JsonSerializable
{
    public function __construct(public int $value)
    {
        parent::__construct(new ResourceChangeId("GuthabenChange"));
    }

    public function __toString(): string
    {
        return '[GuthabenChange: '.$this->value.']';
    }

    public function jsonSerialize(): int
    {
        return $this->value;
    }

    public function accumulate(self $change): self
    {
        return new self($this->value + $change->value);
    }
}
