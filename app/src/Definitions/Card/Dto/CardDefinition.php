<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

use Domain\Definitions\Card\ValueObject\CardId;
use Domain\Definitions\Card\ValueObject\PileId;

final readonly class CardDefinition
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
