<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\Dto;

use Domain\Definitions\Card\ValueObject\PhaseId;

/**
 * Use this interface for cards that are specific to a certain phase
 */
interface CardWithPhase
{
    public function getPhase(): PhaseId;
}
