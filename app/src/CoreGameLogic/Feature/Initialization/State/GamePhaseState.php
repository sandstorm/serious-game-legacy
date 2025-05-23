<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\State;

use Domain\CoreGameLogic\Dto\ValueObject\Konjunkturzyklus;
use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\KonjunkturzyklusWechseln\Event\KonjunkturzyklusWechselExecuted;

class GamePhaseState
{
    public static function isInGamePhase(GameEvents $gameStream): bool
    {
        return $gameStream->findFirstOrNull(GameWasStarted::class) !== null;
    }

    public static function currentKonjunkturzyklus(GameEvents $gameStream): Konjunkturzyklus
    {
        $konjunkturzyklus = self::currentKonjunkturzyklusOrNull($gameStream);
        if ($konjunkturzyklus === null) {
            throw new \RuntimeException('No Konjunkturzyklus found - should never happen.');
        }
        return $konjunkturzyklus;
    }

    public static function currentKonjunkturzyklusOrNull(GameEvents $gameStream): ?Konjunkturzyklus
    {
        return $gameStream->findLastOrNull(KonjunkturzyklusWechselExecuted::class)?->konjunkturzyklus;
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
