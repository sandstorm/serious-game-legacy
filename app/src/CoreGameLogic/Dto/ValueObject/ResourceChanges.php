<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

readonly class ResourceChanges
{
    public function __construct(public int $guthabenChange)
    {
    }

    public function __toString(): string
    {
        return '[guthabenChange: '.$this->guthabenChange.']';
    }

    public function accumulate(self $change): self
    {
        return new self(
            guthabenChange: $this->guthabenChange + $change->guthabenChange
        );
    }
}
