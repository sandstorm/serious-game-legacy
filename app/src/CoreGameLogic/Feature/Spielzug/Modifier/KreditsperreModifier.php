<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Modifier;

use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\Definitions\Card\ValueObject\ModifierId;

/**
 * Player cannot take out a loan. A Konjunkturphase may set this modifier.
 */
readonly final class KreditsperreModifier extends Modifier
{
    public function __construct(
        public PlayerTurn $playerTurn,
        string $description,
    ) {
        parent::__construct(ModifierId::KREDITSPERRE, $playerTurn, $description);
    }

    public function __toString(): string
    {
        return '[ModifierId: ' . $this->id->value . ']';
    }

    public function canModify(HookEnum $hook): bool
    {
        return $hook === HookEnum::KREDITSPERRE;
    }

    /**
     * @param mixed $value has player a kreditsperre (is forbidden to take out a loan)
     * @return bool
     */
    public function modify(mixed $value): bool
    {
        assert(is_bool($value));
        return true;
    }

}
