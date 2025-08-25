<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Modifier;

use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\ModifierId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

/**
 * A Konjunkturphase may modify the (money-) cost of Cards in the "Soziales und Freizeit" pile. The base price is
 * represented by 100 (percent). A value of 105 would represent a 5% increase and 90 a 10% decrease of the cost.
 *
 * The modifier will stay active until the end of the current Konjunkturphase.
 */
readonly final class SozialesUndFreizeitCostModifier extends Modifier
{
    public function __construct(
        public PlayerTurn $playerTurn,
        string $description,
        public int $percentage,
    ) {
        parent::__construct(ModifierId::SOZIALES_UND_FREIZEIT_COST, $playerTurn, $description);
    }

    public function __toString(): string
    {
        return '[ModifierId: ' . $this->id->value . ']';
    }

    public function canModify(HookEnum $hook): bool
    {
        return $hook === HookEnum::SOZIALES_UND_FREIZEIT_COST;
    }

    public function modify(mixed $value): ResourceChanges
    {
        assert($value instanceof ResourceChanges);

        return $value->setGuthabenChange(new MoneyAmount($value->guthabenChange->value * $this->percentage / 100));
    }

}
