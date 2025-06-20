<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet\Event\Behaviour;

use Domain\Definitions\Card\ValueObject\MoneyAmount;

/**
 * This interface is applied on GameEvents which also provide player input.
 */
interface ProvidesPlayerInput
{
    /**
     * The actual player input
     * @return MoneyAmount
     */
    public function getPlayerInput(): MoneyAmount;

    /**
     * The expected input (as calculated by us)
     * @return MoneyAmount
     */
    public function getExpectedInput(): MoneyAmount;

    /**
     * True if actual input matches expected input
     * @return bool
     */
    public function wasInputCorrect(): bool;
}
