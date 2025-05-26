<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\Konjunkturphase;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Konjunkturphase\ValueObject\CurrentYear;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphasenId;

class GamePhaseState
{
    public static function isInGamePhase(GameEvents $gameStream): bool
    {
        return $gameStream->findFirstOrNull(GameWasStarted::class) !== null;
    }

    public static function currentKonjunkturphasenId(GameEvents $gameStream): KonjunkturphasenId
    {
        return $gameStream->findLast(KonjunkturphaseWasChanged::class)->id;
    }

    public static function currentKonjunkturphasenYear(GameEvents $gameStream): CurrentYear
    {
        return $gameStream->findLast(KonjunkturphaseWasChanged::class)->year;
    }

    public static function hasKonjunkturphase(GameEvents $gameStream): bool
    {
        return $gameStream->findFirstOrNull(KonjunkturphaseWasChanged::class) !== null;
    }

    /**
     * @param GameEvents $gameStream
     * @return int[]
     */
    public static function idsOfPastKonjunkturphasen(GameEvents $gameStream): array
    {
        // get all ids of the KonjunkturphaseWechselExecuted events
        $events = $gameStream->findAllOfType(KonjunkturphaseWasChanged::class);
        $ids = [];
        /** @var KonjunkturphaseWasChanged $event */
        foreach ($events as $event) {
            $ids[] = $event->id->value;
        }
        return $ids;
    }
}
