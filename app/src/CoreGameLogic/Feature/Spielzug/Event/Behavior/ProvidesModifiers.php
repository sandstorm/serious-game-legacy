<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior;

use Domain\CoreGameLogic\Feature\Spielzug\Modifier\ModifierCollection;
use Domain\CoreGameLogic\PlayerId;

/**
 * This interface is usually applied on GameEvents which also provide some modifiers.
 *
 * Ein "Modifier" verändert den Spielverlauf in der Zukunft.
 */
interface ProvidesModifiers
{
    public function getModifiers(?PlayerId $playerId = null): ModifierCollection;
}
