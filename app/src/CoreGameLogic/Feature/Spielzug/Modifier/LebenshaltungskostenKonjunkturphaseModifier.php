<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Modifier;

use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Configuration\Configuration;

/**
 * A Konjunkturphase can increase/decrease the Lebenshaltungskosten. This change will be applied multiplicative
 * and affect the {@see Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE} as well as
 * {@see Configuration::LEBENSHALTUNGSKOSTEN_PERCENT}.
 *
 * A value of 100 has no effect.
 * A value of 105 will increase the MinLebenshaltungskosten by 5% (e.g. 5000 € -> 5250 €) and increase the
 * LebenshaltungskostenPercent from 35 to 36.75%
 * A value of 80 will decrease the Lebenshaltungskosten by 20% (e.g. 5000 € -> 4000 €) and decrease the
 * LebenshaltungskostenPercent from 35 to 28%
 *
 * This Modifier will be applied after all other Modifiers for the Lebenshaltungskosten.
 */
readonly final class LebenshaltungskostenKonjunkturphaseModifier extends Modifier
{
    public function __construct(
        public PlayerTurn $playerTurn,
        string $description,
        public float $percentage,
    ) {
        parent::__construct(ModifierId::LEBENSHALTUNGSKOSTEN_KONJUNKTURPHASE_MULTIPLIER, $playerTurn, $description);
    }

    public function __toString(): string
    {
        return '[ModifierId: ' . $this->id->value . ']';
    }

    public function canModify(HookEnum $hook): bool
    {
        return $hook === HookEnum::LEBENSHALTUNGSKOSTEN_MULTIPLIER;
    }

    /**
     * Modifies the Lebenshaltungskosten.
     * @param mixed $value
     * @return float
     */
    public function modify(mixed $value): float
    {
        assert(is_float($value));

        return $value * $this->percentage/100;
    }

}
