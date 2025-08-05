<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Initialization\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Domain\Definitions\Konjunkturphase\ValueObject\KonjunkturphasenId;
use Domain\Definitions\Konjunkturphase\ValueObject\Year;

class GamePhaseState
{
    public static function isInGamePhase(GameEvents $gameEvents): bool
    {
        return $gameEvents->findFirstOrNull(GameWasStarted::class) !== null;
    }

    public static function currentKonjunkturphasenId(GameEvents $gameEvents): KonjunkturphasenId
    {
        return $gameEvents->findLast(KonjunkturphaseWasChanged::class)->id;
    }

    public static function currentKonjunkturphasenYear(GameEvents $gameEvents): Year
    {
        return $gameEvents->findLast(KonjunkturphaseWasChanged::class)->year;
    }

    public static function hasKonjunkturphase(GameEvents $gameEvents): bool
    {
        return $gameEvents->findFirstOrNull(KonjunkturphaseWasChanged::class) !== null;
    }

    /**
     *
     * @param GameEvents $gameEvents
     * @param CategoryId $category
     * @return bool
     */
    public static function hasFreeTimeSlotsForCategory(
        GameEvents $gameEvents,
        CategoryId $category
    ): bool {
        $konjunkturPhaseWasChanged = $gameEvents->findLast(KonjunkturphaseWasChanged::class);
        $players = PreGameState::playersWithNameAndLebensziel($gameEvents);

        $konjunkturphaseDefinition = KonjunkturphaseFinder::findKonjunkturphaseById($konjunkturPhaseWasChanged->id);
        $freeSlots = $konjunkturphaseDefinition->getKompetenzbereichByCategory($category)->zeitslots->getAmountOfZeitslotsForPlayerCount(count($players));

        // now get all players and their placed Zeitsteine in this category
        $usedSlots = 0;
        foreach ($players as $player) {
            $usedSlots += PlayerState::getZeitsteinePlacedForCurrentKonjunkturphaseInCategory($gameEvents, $player->playerId, $category);
        }

        return $freeSlots > $usedSlots;
    }

    /**
     * @param GameEvents $gameEvents
     * @return PlayerId[]
     */
    public static function getOrderedPlayers(GameEvents $gameEvents): array
    {
        $gameWasStarted = $gameEvents->findLast(GameWasStarted::class);
        return $gameWasStarted->playerOrdering;
    }
}
