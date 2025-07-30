<?php

declare(strict_types=1);

namespace Domain\Definitions\Card\ValueObject;

enum LebenszielPhaseId: int
{
    case ANY_PHASE = 0;
    case PHASE_1 = 1;
    case PHASE_2 = 2;
    case PHASE_3 = 3;

    /**
     * Returns true if both Phases are either the same, or one of them is ANY_PHASE.
     * @param LebenszielPhaseId $other
     * @return bool
     */
    public function looselyEquals(LebenszielPhaseId $other): bool
    {
        if ($other->value === LebenszielPhaseId::ANY_PHASE->value) {
            return true;
        }
        return match($this) {
            LebenszielPhaseId::ANY_PHASE => true,
            default => $this->value === $other->value,
        };
    }
}
