<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Modifier;

use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

/**
 * Modifies the Gehalt of the player.
 * A value of 100 means no changes.
 * A value of 105 means a 5% increase.
 * A value of 80 means a 20% decrease.
 *
 * Modifier stays active until the end of the current Konjunkturphase.
 */
readonly final class GehaltModifier extends Modifier
{
    public function __construct(
        public PlayerTurn $playerTurn,
        string $description,
        public int $percentage,
    ) {
        parent::__construct(ModifierId::GEHALT_CHANGE, $playerTurn, $description);
    }

    public function __toString(): string
    {
        return '[ModifierId: ' . $this->id->value . ']';
    }

    public function canModify(HookEnum $hook): bool
    {
        return $hook === HookEnum::GEHALT;
    }

    public function modify(mixed $value): MoneyAmount
    {
        assert($value instanceof MoneyAmount);

        return new MoneyAmount($this->percentage * $value->value / 100);
    }

}
