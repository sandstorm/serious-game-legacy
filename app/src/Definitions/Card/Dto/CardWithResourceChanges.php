<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

/**
 * Use this interface for cards that may provide ResourceChanges
 */
interface CardWithResourceChanges
{
    public function getResourceChanges(): ResourceChanges;
}
