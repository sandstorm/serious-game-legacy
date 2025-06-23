<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\ZeitsteineForPlayer;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseHasEnded;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Konjunkturphase\ValueObject\CurrentYear;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasStartedKonjunkturphase;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerWasMarkedAsReadyForKonjunkturphaseChange;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\Definitions\Configuration\Configuration;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseDefinition;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;

class KonjunkturphaseState
{
    /**
     * @return ZeitsteineForPlayer[]
     */
    public static function calculateInitialZeitsteineForPlayers(GameEvents $gameEvents): array
    {
        $playerIds = $gameEvents->findFirst(GameWasStarted::class)->playerOrdering;
        $numberOfPlayers = count($playerIds);
        $numberOfZeitsteine = match($numberOfPlayers) {
            2 => Configuration::INITIAL_AMOUNT_OF_ZEITSTEINE_FOR_TWO_PLAYERS,
            3, 4 => Configuration::INITIAL_AMOUNT_OF_ZEITSTEINE_FOR_THREE_OR_FOUR_PLAYERS,
            default => throw new \RuntimeException('Number of players not supported', 1748866080)
        };
        $zeitsteineForPlayers = [];
        foreach ($playerIds as $playerId) {
            $zeitsteineForPlayers[$playerId->value] = new ZeitsteineForPlayer(
                $playerId,
                $numberOfZeitsteine,
            );
        }
        return $zeitsteineForPlayers;
    }

    /**
     * Returns true if the condition for the end of the current Konjunkturphase is met.
     * Currently this means no player has any Zeitsteine left. This is used to decide if
     * we end the current Konjunkturphase.
     * @param GameEvents $gameEvents
     * @return bool
     */
    public static function isConditionForEndOfKonjunkturphaseMet(GameEvents $gameEvents): bool
    {
        $playerIds = $gameEvents->findFirst(GameWasStarted::class)->playerOrdering;
        $totalNumberOfZeitsteine = 0;
        foreach ($playerIds as $playerId) {
            $totalNumberOfZeitsteine = $totalNumberOfZeitsteine + PlayerState::getZeitsteineForPlayer($gameEvents,
                    $playerId);
        }
        // TODO we may need to safeguard against negative values at some point (probably not here though)
        assert($totalNumberOfZeitsteine >= 0);
        return $totalNumberOfZeitsteine === 0;
    }

    /**
     * Returns true, if a KonjunkturphaseHasEnded Event exists for the current Konjunkturphase.
     * @param GameEvents $gameEvents
     * @return bool
     */
    public static function hasCurrentKonjunkturphaseEnded(GameEvents $gameEvents): bool
    {
        /** @var KonjunkturphaseHasEnded $lastKonjunkturphaseHasEndedEvent */
        $lastKonjunkturphaseHasEndedEvent = $gameEvents->findLastOrNull(KonjunkturphaseHasEnded::class);
        if ($lastKonjunkturphaseHasEndedEvent === null) {
            return false;
        }

        /** @var KonjunkturphaseWasChanged $lastKonjunkturphaseWasChangedEvent */
        $lastKonjunkturphaseWasChangedEvent = $gameEvents->findLast(KonjunkturphaseWasChanged::class);

        return $lastKonjunkturphaseWasChangedEvent->year->value === $lastKonjunkturphaseHasEndedEvent->year->value;
    }

    public static function getCurrentKonjunkturphase(GameEvents $gameEvents): KonjunkturphaseDefinition
    {
        return KonjunkturphaseFinder::findKonjunkturphaseById($gameEvents->findLast(KonjunkturphaseWasChanged::class)->id);
    }

    public static function getCurrentYear(GameEvents $gameEvents): CurrentYear
    {
        $lastKonjunkturphaseWasChangedEvent = $gameEvents->findLast(KonjunkturphaseWasChanged::class);
        return $lastKonjunkturphaseWasChangedEvent->year;
    }

    public static function hasPlayerStartedCurrentKonjunkturphase(GameEvents $gameEvents, PlayerId $playerId): bool
    {
        /** @var PlayerHasStartedKonjunkturphase $latestPlayerHasStartedKonjunkturphaseEvent */
        $latestPlayerHasStartedKonjunkturphaseEvent = $gameEvents->findLastOrNullWhere(
            fn($event) => $event instanceof PlayerHasStartedKonjunkturphase && $event->playerId->equals($playerId));

        if ($latestPlayerHasStartedKonjunkturphaseEvent === null) {
            return false;
        }
        $latestKonjunkturphaseWasChangedEvent = $gameEvents->findLast(KonjunkturphaseWasChanged::class);
        return $latestPlayerHasStartedKonjunkturphaseEvent->year->equals($latestKonjunkturphaseWasChangedEvent->year);
    }

    public static function isPlayerReadyForKonjunkturphaseChange(GameEvents $gameEvents, PlayerId $playerId): bool
    {
        return $gameEvents->findLastOrNullWhere(
            fn($event) => $event instanceof PlayerWasMarkedAsReadyForKonjunkturphaseChange
                && $event->year->equals(KonjunkturphaseState::getCurrentYear($gameEvents))
                && $event->playerId->equals($playerId)) !== null;
    }

    public static function areAllPlayersMarkedAsReadyForKonjunkturphaseChange(GameEvents $gameEvents): bool
    {
        $players = PreGameState::playersWithNameAndLebensziel($gameEvents);
        $areAllPlayersReady = true;
        foreach ($players as $player) {
            $areAllPlayersReady = $areAllPlayersReady
                && KonjunkturphaseState::isPlayerReadyForKonjunkturphaseChange($gameEvents, $player->playerId);
        }
        return $areAllPlayersReady;
    }
}
