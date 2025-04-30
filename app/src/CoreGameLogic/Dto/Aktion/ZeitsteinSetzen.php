<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\Aktion;

use Domain\CoreGameLogic\Dto\Event\EventStream;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;

class ZeitsteinSetzen extends Aktion
{
    public function __construct()
    {
        parent::__construct('zeitstein-setzen', 'Zeitstein setzen');
    }

    public function canExecute(PlayerId $player, EventStream $eventStream): bool
    {
        return true;
    }

    public function execute(PlayerId $player, EventStream $eventStream): EventStream
    {
        // TODO: Implement execute() method.
    }
}
