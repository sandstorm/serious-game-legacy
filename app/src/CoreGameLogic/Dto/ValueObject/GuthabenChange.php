<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\ValueObject;

readonly class GuthabenChange extends Modifier
{
    public function __construct(public int $guthabenChange)
    {
        parent::__construct(new ModifierId("GuthabenChange"));
    }
}
