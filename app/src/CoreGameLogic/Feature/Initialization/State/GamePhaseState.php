<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Konjunkturphase\ValueObject\CurrentYear;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
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
     *
     * @param GameEvents $gameStream
     * @param CategoryId $category
     * @return bool
     */
    public static function hasFreeTimeSlotsForCategory(
        GameEvents $gameStream,
        CategoryId $category
    ): bool {
        $konjunkturPhaseWasChanged = $gameStream->findLast(KonjunkturphaseWasChanged::class);
        $freeSlots = collect($konjunkturPhaseWasChanged->kompetenzbereiche)
            ->firstWhere('name', $category)->kompetenzsteine ?? 0;

        // now get all players and their placed Zeitsteine in this category
        $players = PreGameState::playersWithNameAndLebensziel($gameStream);
        $usedSlots = 0;
        foreach ($players as $player) {
            $usedSlots += PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($gameStream, $player->playerId, $category);
        }

        return $freeSlots > $usedSlots;
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
