<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Modifier;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Configuration\Configuration;

/**
 * Having a child increases the Lebenshaltungskosten by a fixed percentage amount. The value will be added to the
 * @see Configuration::LEBENSHALTUNGSKOSTEN_PERCENT
 *
 * An additionalPercentage of 10 will result in a total percentage of 45 (assuming that the base percentage is 35)
 */
readonly final class AdditionalLebenshaltungskostenKindModifier extends Modifier
{
    public function __construct(
        public PlayerTurn $playerTurn,
        string $description,
        public float $additionalPercentage,
    ) {
        parent::__construct(ModifierId::LEBENSHALTUNGSKOSTEN_KIND_INCREASE, $playerTurn, $description);
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
        return $hook === HookEnum::LEBENSHALTUNGSKOSTEN_PERCENT_INCREASE;
    }

    /**
     * Modifies the percentage for the Lebenshaltungskosten.
     * @param mixed $value
     * @return float
     */
    public function modify(mixed $value): float
    {
        assert(is_float($value));

        return $value + $this->additionalPercentage;
    }

}
