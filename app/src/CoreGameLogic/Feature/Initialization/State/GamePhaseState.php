<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Initialization\Event\KonjunkturzyklusWechselExecuted;

class GamePhaseState
{

    public static function isInGamePhase(GameEvents $gameStream): bool
    {
        return $gameStream->findFirstOrNull(GameWasStarted::class) !== null;
    }

    public static function currentYear(GameEvents $gameStream): ?KonjunkturzyklusWechselExecuted
    {
        return $gameStream->findLastOrNull(KonjunkturzyklusWechselExecuted::class);
    }
}
