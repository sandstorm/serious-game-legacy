<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Modifier;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\Definitions\Card\ValueObject\ModifierId;

readonly final class EmptyModifier extends Modifier
{
    public function __construct(
    ) {
        parent::__construct(ModifierId::EMPTY, new PlayerTurn(0), 'Leer');
    }

    public function __toString(): string
    {
        return '[ModifierId: ' . $this->id->value . ']';
    }

    public function isActive(GameEvents $gameEvents): bool
    {
        return false;
    }

    public function canModify(HookEnum $hook): bool
    {
        return false;
    }

    public function modify(mixed $value): bool
    {
        assert(is_bool($value));
        return true;
    }

}
