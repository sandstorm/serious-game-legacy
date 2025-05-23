<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\State;

use Domain\CoreGameLogic\Dto\ValueObject\Konjunkturphase;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasSwitched;

class GamePhaseState
{
    public static function isInGamePhase(GameEvents $gameStream): bool
    {
        return $gameStream->findFirstOrNull(GameWasStarted::class) !== null;
    }

    public static function currentKonjunkturphase(GameEvents $gameStream): Konjunkturphase
    {
        $konjunkturphase = self::currentKonjunkturphaseOrNull($gameStream);
        if ($konjunkturphase === null) {
            throw new \RuntimeException('No Konjunkturphase found - should never happen.');
        }
        return $konjunkturphase;
    }

    public static function currentKonjunkturphaseOrNull(GameEvents $gameStream): ?Konjunkturphase
    {
        return $gameStream->findLastOrNull(KonjunkturphaseWasSwitched::class)?->konjunkturphase;
    }

    /**
     * @param GameEvents $gameStream
     * @return int[]
     */
    public static function idsOfPastKonjunkturphasen(GameEvents $gameStream): array
    {
        // get all ids of the KonjunkturphaseWechselExecuted events
        $events = $gameStream->findAllOfType(KonjunkturphaseWasSwitched::class);
        $ids = [];
        /** @var KonjunkturphaseWasSwitched $event */
        foreach ($events as $event) {
            $ids[] = $event->konjunkturphase->id;
        }
        return $ids;
    }
}
