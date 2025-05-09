<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\Event\Player;

use Domain\CoreGameLogic\Dto\ValueObject\ModifierCollection;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEventInterface;

/**
 * This interface is usually applied on GameEvents which also provide some modifiers.
 *
 * Ein "Modifier" verändert den Spielverlauf in der Zukunft.
 */
interface ProvidesModifiers
{
    public function getModifiers(PlayerId $playerId): ModifierCollection;
}
