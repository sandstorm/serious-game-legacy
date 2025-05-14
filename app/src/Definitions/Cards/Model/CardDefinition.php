<?php

declare(strict_types=1);

namespace Domain\Definitions\Cards\Model;

use Domain\CoreGameLogic\Dto\ValueObject\CardId;
use Domain\CoreGameLogic\Dto\ValueObject\ResourceChangeCollection;

class CardDefinition
{
    public function __construct(
        public CardId $id,
        public ResourceChangeCollection $resourceChanges,
    )
    {

    }
}
