<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\LebenshaltungskostenForPlayerWereEntered;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\SteuernUndAbgabenForPlayerWereEntered;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobOfferWasAccepted;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;

class MoneySheetState
{
    public static function calculateLebenshaltungskostenForPlayer(GameEvents $gameEvents, PlayerId $playerId): float
    {
        $minKosten = 5000;
        $gehalt = PlayerState::getGehaltForPlayer($gameEvents, $playerId);
        return max([round($gehalt * 0.35, 2), $minKosten]);
    }

    public static function calculateSteuernUndAbgabenForPlayer(GameEvents $gameEvents, PlayerId $playerId): float
    {
        $gehalt = PlayerState::getGehaltForPlayer($gameEvents, $playerId);
        return round($gehalt * 0.25, 2);
    }

    public static function getLastEnteredLebenshaltungskostenForPlayer(GameEvents $gameEvents, PlayerId $playerId): ?float
    {
        /** @var LebenshaltungskostenForPlayerWereEntered|null $lastEvent */
        $lastEvent = $gameEvents->findLastOrNullWhere(
            fn($event) => $event instanceof LebenshaltungskostenForPlayerWereEntered && $event->playerId->equals($playerId)
        );

        return $lastEvent?->getPlayerInput();
    }

    public static function getLastEnteredSteuernUndAbgabenForPlayer(GameEvents $gameEvents, PlayerId $playerId): ?float
    {
        /** @var SteuernUndAbgabenForPlayerWereEntered|null $lastEvent */
        $lastEvent = $gameEvents->findLastOrNullWhere(
            fn($event) => $event instanceof SteuernUndAbgabenForPlayerWereEntered && $event->playerId->equals($playerId)
        );

        return $lastEvent?->getPlayerInput();
    }

    private static function getEventsSinceLastGehaltChangeForPlayer(GameEvents $gameEvents, PlayerId $playerId): GameEvents
    {
        // TODO We may need to change this later (e.g. quit job, modifiers)
        $eventsAfterLastGehaltChange = $gameEvents->findAllAfterLastOrNullWhere(
            fn($event) => $event instanceof JobOfferWasAccepted && $event->playerId->equals($playerId));
        if ($eventsAfterLastGehaltChange === null) {
            $eventsAfterLastGehaltChange = $gameEvents->findAllAfterLastOfType(GameWasStarted::class);
        }
        return $eventsAfterLastGehaltChange;
    }

    /**
     * Returns the number of tries since the last time the Gehalt changed.
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return int
     */
    public static function getNumberOfTriesForSteuernUndAbgabenInput(GameEvents $gameEvents, PlayerId $playerId): int
    {
        $eventsAfterLastGehaltChange = self::getEventsSinceLastGehaltChangeForPlayer($gameEvents, $playerId);
        // Gather all relevant input events for the player
        $tries = $eventsAfterLastGehaltChange->findAllOfType(SteuernUndAbgabenForPlayerWereEntered::class)
            ->filter(fn(SteuernUndAbgabenForPlayerWereEntered $event) => $event->playerId->equals($playerId));

        return count($tries);
    }

    public static function getNumberOfTriesForLebenshaltungskostenInput(GameEvents $gameEvents, PlayerId $playerId): int
    {
        $eventsAfterLastGehaltChange = self::getEventsSinceLastGehaltChangeForPlayer($gameEvents, $playerId);
        $tries = $eventsAfterLastGehaltChange->findAllOfType(LebenshaltungskostenForPlayerWereEntered::class)
            ->filter(fn(LebenshaltungskostenForPlayerWereEntered $event) => $event->playerId->equals($playerId));

        return count($tries);
    }
}
