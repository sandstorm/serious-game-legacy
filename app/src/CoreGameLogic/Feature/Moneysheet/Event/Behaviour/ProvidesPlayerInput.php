<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet\Event\Behaviour;

/**
 * This interface is usually applied on GameEvents which also provide some modifiers.
 *
 * Ein "Modifier" verändert den Spielverlauf in der Zukunft.
 */
interface ProvidesPlayerInput
{
    public function getPlayerInput(): float;
    public function getExpectedInput(): float;
    public function wasInputCorrect(): bool;
}
