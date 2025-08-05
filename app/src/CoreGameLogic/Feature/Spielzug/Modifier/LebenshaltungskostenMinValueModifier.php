<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Modifier;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Configuration\Configuration;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

/**
 * Currently only used when having a child, which increases the multiplier for the Lebenshaltungskosten by a fixed
 * amount. The value will be added to the @see Configuration::LEBENSHALTUNGSKOSTEN_MIN_VALUE
 */
readonly final class LebenshaltungskostenMinValueModifier extends Modifier
{
    public function __construct(
        public PlayerId $playerId,
        public PlayerTurn $playerTurn,
        string $description,
        public Year $activeYear,
        public MoneyAmount $minValueChange,
    ) {
        parent::__construct(ModifierId::LEBENSHALTUNGSKOSTEN_MIN_VALUE, $playerTurn, $description);
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
        return $hook === HookEnum::LEBENSHALTUNGSKOSTEN_MIN_VALUE;
    }

    /**
     * Modifies the minimum value for Lebenshaltungskosten.
     * @param mixed $value
     * @return MoneyAmount
     */
    public function modify(mixed $value): MoneyAmount
    {
        assert($value instanceof MoneyAmount);

        return $value->add($this->minValueChange);
    }

}
