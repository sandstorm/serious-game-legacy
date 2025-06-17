<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet\Event\Behaviour;

/**
 * This interface is applied on GameEvents which also provide player input.
 */
interface ProvidesPlayerInput
{
    /**
     * The actual player input
     * @return float
     */
    public function getPlayerInput(): float;

    /**
     * The expected input (as calculated by us)
     * @return float
     */
    public function getExpectedInput(): float;

    /**
     * True if actual input matches expected input
     * @return bool
     */
    public function wasInputCorrect(): bool;
}
