<?php

declare(strict_types=1);

namespace Domain\Definitions\Cards\Model;

use Domain\CoreGameLogic\Dto\ValueObject\CardId;
use Domain\CoreGameLogic\Dto\ValueObject\PileId;
use Domain\CoreGameLogic\Dto\ValueObject\CardRequirements;
use Domain\CoreGameLogic\Dto\ValueObject\ResourceChanges;

class CardDefinition
{
    public function __construct(
        public CardId $id,
        public PileId $pileId,
        public string $kurzversion,
        public string $langversion,
        public ResourceChanges $resourceChanges,
        public CardRequirements $requirements,
    ) {
    }
}
