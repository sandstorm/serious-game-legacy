<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Modifier;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

/**
 * Having a child increases the multiplier for the Lebenshaltungskosten by a fixed amount. The value will be added to the
 * @see Configuration::LEBENSHALTUNGSKOSTEN_MULTIPLIER
 *
 */
readonly final class LebenshaltungskostenMultiplierModifier extends Modifier
{
    public function __construct(
        public PlayerId $playerId,
        public PlayerTurn $playerTurn,
        string $description,
        public Year $activeYear,
        public float $multiplier,
    ) {
        parent::__construct(ModifierId::LEBENSHALTUNGSKOSTEN_MULTIPLIER, $playerTurn, $description);
    }

    public function __toString(): string
    {
        return '[ModifierId: ' . $this->id->value . ']';
    }

    /**
     * This modifier will stay active for the rest of the game
     * @param GameEvents $gameEvents
     * @return bool
     */
    public function isActive(GameEvents $gameEvents): bool
    {
        return true;
    }

    public function canModify(HookEnum $hook): bool
    {
        return $hook === HookEnum::LEBENSHALTUNGSKOSTEN_MULTIPLIER;
    }

    /**
     * Modifies the **multiplier** for the Lebenshaltungskosten.
     * @param mixed $value
     * @return float
     */
    public function modify(mixed $value): float
    {
        assert(is_float($value));

        return $value + $this->multiplier/100;
    }

}
