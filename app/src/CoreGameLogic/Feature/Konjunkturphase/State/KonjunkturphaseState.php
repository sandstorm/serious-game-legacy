<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Dto\ZeitsteineForPlayer;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseHasEnded;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
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
            2 => 6,
            3 => 5,
            4 => 4,
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
     * Currently this means no player has any Zeitsteine left.
     * @param GameEvents $gameEvents
     * @return bool
     */
    public static function isEndOfKonjunkturphase(GameEvents $gameEvents): bool
    {
        $playerIds = $gameEvents->findFirst(GameWasStarted::class)->playerOrdering;
        $totalNumberOfZeitsteine = 0;
        foreach ($playerIds as $playerId) {
            $totalNumberOfZeitsteine = $totalNumberOfZeitsteine + PlayerState::getZeitsteineForPlayer($gameEvents, $playerId);
        }
        // TODO we may need to safeguard against negative values at some point (probably not here though)
        assert($totalNumberOfZeitsteine >= 0);
        return $totalNumberOfZeitsteine === 0;
    }

    public static function isKonjunkturphaseEnding(GameEvents $gameEvents): bool
    {
        $konjunkturphaseHasEndedEvents = $gameEvents->findAllOfType(KonjunkturphaseHasEnded::class);
        $konjunkturphaseWasChangedEvents = $gameEvents->findAllOfType(KonjunkturphaseWasChanged::class);
        return count($konjunkturphaseWasChangedEvents) === count($konjunkturphaseHasEndedEvents);
    }

    public static function getCurrentKonjunkturphase(GameEvents $gameEvents): KonjunkturphaseDefinition
    {
        return KonjunkturphaseFinder::findKonjunkturphaseById($gameEvents->findLast(KonjunkturphaseWasChanged::class)->id);
    }
}
