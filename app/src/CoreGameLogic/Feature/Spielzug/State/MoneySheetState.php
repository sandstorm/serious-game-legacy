<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\PlayerId;

class MoneySheetState
{
    public static function lebenskostenForPlayer(GameEvents $stream, PlayerId $playerId): int
    {
        return 0;
    }
}
