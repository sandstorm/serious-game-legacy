<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Modifier;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\ValueObject\Year;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

readonly final class GehaltModifier extends Modifier
{
    public function __construct(
        public PlayerId $playerId,
        public PlayerTurn $playerTurn,
        string $description,
        public Year $activeYear,
        public int $percentage,
    ) {
        parent::__construct(ModifierId::GEHALT_CHANGE, $playerTurn, $description);
    }

    public function __toString(): string
    {
        return '[ModifierId: ' . $this->id->value . ']';
    }

    public function isActive(GameEvents $gameEvents): bool
    {
        return KonjunkturphaseState::getCurrentYear($gameEvents)->equals($this->activeYear);
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
