<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Moneysheet\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Moneysheet\Dto\InputResult;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\Behaviour\UpdatesInputForLebenshaltungskosten;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\Behaviour\UpdatesInputForSteuernUndAbgaben;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\LebenshaltungskostenForPlayerWereCorrected;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\LebenshaltungskostenForPlayerWereEntered;
use Domain\CoreGameLogic\Feature\Moneysheet\Event\SteuernUndAbgabenForPlayerWereCorrected;
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

    private static function getEventsSinceLastGehaltChangeForPlayer(
        GameEvents $gameEvents,
        PlayerId $playerId
    ): GameEvents {
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

    public static function getResultOfLastSteuernUndAbgabenInput(
        GameEvents $gameEvents,
        PlayerId $playerId
    ): InputResult {
        $lastInputEventForPlayer = $gameEvents->findLastOrNullWhere(
            fn($event) => $event instanceof UpdatesInputForSteuernUndAbgaben && $event->getPlayerId()->equals($playerId));

        if ($lastInputEventForPlayer === null) {
            // No error message before first input
            return new InputResult(wasSuccessful: true);
        }

        if ($lastInputEventForPlayer instanceof SteuernUndAbgabenForPlayerWereEntered) {
            return new InputResult($lastInputEventForPlayer->wasInputCorrect());
        } // after this we know that the input was NOT successful

        if ($lastInputEventForPlayer instanceof SteuernUndAbgabenForPlayerWereCorrected) {
            // multiply resource change with -1 to get a positive value
            return new InputResult(false, -1 * $lastInputEventForPlayer->getResourceChanges($playerId)->guthabenChange);
        }
        throw new \RuntimeException("Unknown Event type " . $lastInputEventForPlayer::class);
    }

    public static function getResultOfLastLebenshaltungskostenInput(
        GameEvents $gameEvents,
        PlayerId $playerId
    ): InputResult {
        $lastInputEventForPlayer = $gameEvents->findLastOrNullWhere(
            fn($event) => $event instanceof UpdatesInputForLebenshaltungskosten && $event->getPlayerId()->equals($playerId));

        if ($lastInputEventForPlayer === null) {
            // No error message before first input
            return new InputResult(wasSuccessful: true);
        }

        if ($lastInputEventForPlayer instanceof LebenshaltungskostenForPlayerWereEntered) {
            return new InputResult($lastInputEventForPlayer->wasInputCorrect());
        } // after this we know that the input was NOT successful

        if ($lastInputEventForPlayer instanceof LebenshaltungskostenForPlayerWereCorrected) {
            // multiply resource change with -1 to get a positive value
            return new InputResult(false, -1 * $lastInputEventForPlayer->getResourceChanges($playerId)->guthabenChange);
        }
        throw new \RuntimeException("Unknown Event type " . $lastInputEventForPlayer::class);
    }

    public static function getLastInputForSteuernUndAbgaben(GameEvents $gameEvents, PlayerId $myself): float
    {
        /** @var UpdatesInputForSteuernUndAbgaben|null $lastInputEvent @phpstan-ignore varTag.type */
        $lastInputEvent = $gameEvents->findLastOrNullWhere(
            fn($event) => $event instanceof UpdatesInputForSteuernUndAbgaben && $event->getPlayerId()->equals($myself));
        return $lastInputEvent === null ? 0 : $lastInputEvent->getUpdatedValue();
    }

    public static function getLastInputForLebenshaltungskosten(GameEvents $gameEvents, PlayerId $myself): float
    {
        /** @var UpdatesInputForLebenshaltungskosten|null $lastInputEvent @phpstan-ignore varTag.type */
        $lastInputEvent = $gameEvents->findLastOrNullWhere(
            fn($event) => $event instanceof UpdatesInputForLebenshaltungskosten && $event->getPlayerId()->equals($myself));
        return $lastInputEvent === null ? 0 : $lastInputEvent->getUpdatedValue();
    }
}
