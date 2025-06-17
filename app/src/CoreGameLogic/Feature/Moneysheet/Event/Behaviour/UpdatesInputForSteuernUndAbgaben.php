<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet\Event\Behaviour;

use Domain\CoreGameLogic\PlayerId;

/**
 * This interface is applied on GameEvents which change the Input value for Steuern und Abgaben.
 */
interface UpdatesInputForSteuernUndAbgaben
{
    public function getPlayerId(): PlayerId;
    public function getUpdatedValue(): float;
}
