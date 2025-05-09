<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\Aktion;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;

class ZeitsteinSetzen extends Aktion
{
    public function __construct()
    {
        parent::__construct('zeitstein-setzen', 'Zeitstein setzen');
    }

    public function canExecute(PlayerId $player, GameEvents $eventStream): bool
    {
        return true;
    }

    public function execute(PlayerId $player, GameEvents $eventStream): GameEvents
    {
        // TODO: Implement execute() method.
        return GameEvents::fromArray([]);
    }
}
