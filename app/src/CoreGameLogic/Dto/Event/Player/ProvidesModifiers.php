<?php

namespace Domain\CoreGameLogic\Dto\Event\Player;

use Domain\CoreGameLogic\Dto\ValueObject\ModifierCollection;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;

interface ProvidesModifiers
{
    public function getModifiers(PlayerId $playerId): ModifierCollection;
}
