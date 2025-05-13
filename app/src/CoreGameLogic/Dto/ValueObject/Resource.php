<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

readonly abstract class Resource
{
    public function __construct(public ResourceId $id)
    {
    }

    public function __toString(): string
    {
        return '[ResourceId: '.$this->id.']';
    }

}
