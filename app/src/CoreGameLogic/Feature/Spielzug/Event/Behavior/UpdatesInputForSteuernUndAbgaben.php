<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior;

use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

/**
 * This interface is applied on GameEvents which change the Input value for Steuern und Abgaben.
 */
interface UpdatesInputForSteuernUndAbgaben
{
    public function getPlayerId(): PlayerId;
    public function getUpdatedValue(): MoneyAmount;
}
