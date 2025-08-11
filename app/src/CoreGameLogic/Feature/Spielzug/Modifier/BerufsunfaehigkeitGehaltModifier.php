<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Modifier;

use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

readonly final class BerufsunfaehigkeitGehaltModifier extends Modifier
{
    public function __construct(
        public PlayerId $playerId,
        public PlayerTurn $playerTurn,
        string $description,
    ) {
        parent::__construct(ModifierId::BERUFSUNFAEHIGKEIT_GEHALT, $playerTurn, $description);
    }

    public function __toString(): string
    {
        return '[ModifierId: ' . $this->id->value . ']';
    }

    public function canModify(HookEnum $hook): bool
    {
        return $hook === HookEnum::BERUFSUNFAEHIGKEIT_GEHALT;
    }

    public function modify(mixed $value): MoneyAmount
    {
        assert($value instanceof MoneyAmount);

        return $value;
    }

}
