<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\KonjunkturzyklusWechseln\Event\KonjunkturzyklusWechselExecuted;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;

class GamePhaseState
{
    public static function isInGamePhase(GameEvents $gameStream): bool
    {
        return $gameStream->findFirstOrNull(GameWasStarted::class) !== null;
    }

    public static function currentKonjunkturzyklus(GameEvents $gameStream): ?KonjunkturzyklusWechselExecuted
    {
        return $gameStream->findLastOrNull(KonjunkturzyklusWechselExecuted::class);
    }

    /**
     * @param GameEvents $gameStream
     * @return int[]
     */
    public static function idsOfPastKonjunkturzyklen(GameEvents $gameStream): array
    {
        // get all ids of the KonjunkturzyklusWechselExecuted events
        $events = $gameStream->findAllOfType(KonjunkturzyklusWechselExecuted::class);
        $ids = [];
        /** @var KonjunkturzyklusWechselExecuted $event */
        foreach ($events as $event) {
            $ids[] = $event->konjunkturzyklus->id;
        }
        return $ids;
    }
}
