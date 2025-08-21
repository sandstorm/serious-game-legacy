<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

use Domain\Definitions\Card\ValueObject\CardId;

/**
 * Use this interface for cards that require a specific card.
 */
interface CardWithRequiredCard
{
    public function getRequiredCardId(): CardId|null;
}
