<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\ValueObject;

enum PhaseId: int
{
    case PHASE_1 = 1;
    case PHASE_2 = 2;
    case PHASE_3 = 3;
}
