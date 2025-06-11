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
    public function getPlayerInput(): int;
    public function getExpectedInput(): int;
    public function wasInputCorrect(): bool;
}
