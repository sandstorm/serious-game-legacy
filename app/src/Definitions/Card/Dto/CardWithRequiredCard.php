<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

/**
 * Use this interface for cards that require a specific card.
 */
interface CardWithRequiredCard
{
    public function getRequiredCard(): string;
}
