<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

readonly abstract class ResourceChange
{
    public function __construct(public ResourceChangeId $id)
    {
    }

    public function __toString(): string
    {
        return '[ResourceChangeId: '.$this->id.']';
    }

}
