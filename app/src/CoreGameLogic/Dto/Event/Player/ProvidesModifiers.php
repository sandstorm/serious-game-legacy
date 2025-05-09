<?php

namespace Domain\CoreGameLogic\Dto\Event\Player;

use Domain\CoreGameLogic\Dto\ValueObject\ModifierCollection;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEventInterface;

interface ProvidesModifiers extends GameEventInterface
{
    public function getModifiers(PlayerId $playerId): ModifierCollection;
}
