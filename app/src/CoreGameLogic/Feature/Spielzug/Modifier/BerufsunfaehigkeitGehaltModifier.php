<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Modifier;

use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

/**
 * This modifier is used when the player draws an EreignisCard that will cause them to lose their job but allows
 * a Berufsunfähigkeitsversicherung to mitigate the consequences. If the player has a Berufsunfähigkeitsversicherung
 * at the time of the EreignisCard, then the current Gehalt will be payed one last time at the end of the current
 * Konjunkturphase.
 * The modifier will stay active until the end of the current Konjunkturphase.
 */
readonly final class BerufsunfaehigkeitGehaltModifier extends Modifier
{
    public function __construct(
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
