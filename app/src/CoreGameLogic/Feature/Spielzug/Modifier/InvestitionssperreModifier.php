<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Modifier;

use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\Definitions\Card\ValueObject\ModifierId;

/**
 * Used after the player draws an EreignisCard that disallows investing. While this modifier is active, the player
 * cannot buy or sell any stocks/etfs/crypto/immobilien. Stays active until the end of the current Konjunkturphase.
 */
final readonly class InvestitionssperreModifier extends Modifier
{
    public function __construct(
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
