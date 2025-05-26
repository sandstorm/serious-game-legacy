<?php

declare(strict_types=1);

namespace Domain\Definitions\Cards\Dto;

use Domain\CoreGameLogic\Dto\ValueObject\CardRequirements;
use Domain\CoreGameLogic\Dto\ValueObject\ResourceChanges;
use Domain\Definitions\Cards\ValueObject\CardId;
use Domain\Definitions\Cards\ValueObject\PileId;

class CardDefinition
{
    public function __construct(
        public CardId $id,
        public PileId $pileId,
        public string $kurzversion,
        public string $langversion,
        public ResourceChanges $resourceChanges,
        public CardRequirements $additionalRequirements,
    ) {
    }
}
