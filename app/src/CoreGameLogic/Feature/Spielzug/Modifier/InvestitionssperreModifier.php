<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Modifier;

use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\ModifierId;

readonly final class InvestitionssperreModifier extends Modifier
{
    public function __construct(
        public PlayerId $playerId,
        public PlayerTurn $playerTurn,
        string $description,
    ) {
        parent::__construct(ModifierId::INVESTITIONSSPERRE, $playerTurn, $description);
    }

    public function __toString(): string
    {
        return '[ModifierId: ' . $this->id->value . ']';
    }

    public function canModify(HookEnum $hook): bool
    {
        return $hook === HookEnum::INVESTITIONSSPERRE;
    }

    public function modify(mixed $value): bool
    {
        assert(is_bool($value));
        return true;
    }

}
