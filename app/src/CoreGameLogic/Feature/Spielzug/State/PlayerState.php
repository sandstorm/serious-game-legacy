<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\PlayerColorWasSelected;
use Domain\CoreGameLogic\Feature\Initialization\Event\PreGameStarted;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobWasQuit;
use Domain\CoreGameLogic\Feature\Spielzug\Event\MinijobWasDone;
use Domain\Definitions\Card\Dto\MinijobCardDefinition;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ZeitsteinAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobOfferWasAccepted;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use RuntimeException;

class PlayerState
{
    public static function getPlayerColor(GameEvents $stream, PlayerId $playerId): string|null
    {
        $playerColor = $stream->findAllOfType(PlayerColorWasSelected::class);
        /** @var PlayerColorWasSelected $event **/
        foreach ($playerColor as $event) {
            if ($event->playerId->equals($playerId)) {
                return $event->playerColor->value;
            }
        }

        return null;
    }

    public static function getResourcesForPlayer(GameEvents $stream, PlayerId $playerId): ResourceChanges
    {
        $accumulatedResources = $stream->findAllOfType(ProvidesResourceChanges::class)
            ->reduce(fn(ResourceChanges $accumulator, ProvidesResourceChanges $event) => $accumulator->accumulate($event->getResourceChanges($playerId)), new ResourceChanges());
        return new ResourceChanges(
            guthabenChange: $accumulatedResources->guthabenChange,
            zeitsteineChange: self::getZeitsteineForPlayer($stream, $playerId),
            bildungKompetenzsteinChange: $accumulatedResources->bildungKompetenzsteinChange,
            freizeitKompetenzsteinChange: $accumulatedResources->freizeitKompetenzsteinChange,
        );
    }

    /**
     * Returns the current amount of Zeitsteine available to the player.
     */
    public static function getZeitsteineForPlayer(GameEvents $stream, PlayerId $playerId): int
    {
        if (!in_array(needle: $playerId, haystack: $stream->findLast(PreGameStarted::class)->playerIds, strict: true)) {
            throw new RuntimeException('Player ' . $playerId . ' does not exist', 1748432811);
        }
        $accumulatedResourceChangesForPlayer = $stream->findAllAfterLastOfType(KonjunkturphaseWasChanged::class)->findAllOfType(ProvidesResourceChanges::class)
            ->reduce(fn(ResourceChanges $accumulator, ProvidesResourceChanges $event) => $accumulator->accumulate($event->getResourceChanges($playerId)), new ResourceChanges());

        $zeitsteineForPlayer = $accumulatedResourceChangesForPlayer->accumulate(new ResourceChanges(zeitsteineChange: +self::getInitialZeitsteineForKonjunkturphase($stream, $playerId)))->zeitsteineChange;
        return ModifierCalculator::forStream($stream)->forPlayer($playerId)->applyToAvailableZeitsteine($zeitsteineForPlayer);
    }

    /**
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return int
     */
    private static function getInitialZeitsteineForKonjunkturphase(GameEvents $gameEvents, PlayerId $playerId): int
    {
        $zeitsteineForPlayers = $gameEvents->findLast(KonjunkturphaseWasChanged::class)->zeitsteineForPlayers;
        // TODO make this safer (...[0] may not work)
        return array_values(array_filter($zeitsteineForPlayers, fn ($forPlayer) => $forPlayer->playerId->equals($playerId)))[0]->zeitsteine;
    }

    /**
     * Returns the current Guthaben of the player.
     */
    public static function getGuthabenForPlayer(GameEvents $stream, PlayerId $playerId): MoneyAmount
    {
        // TODO: wenig Typisierung hier -> gibt alles plain values etc zurück.
        $accumulatedResourceChangesForPlayer = $stream->findAllOfType(ProvidesResourceChanges::class)
            ->reduce(fn(ResourceChanges $accumulator, ProvidesResourceChanges $event) => $accumulator->accumulate($event->getResourceChanges($playerId)), new ResourceChanges());

        return $accumulatedResourceChangesForPlayer->guthabenChange;
    }

    /**
     * Returns the accumulated amount of Bildungskompetenzsteine of the player.
     *
     * @param GameEvents $stream
     * @param PlayerId $playerId
     * @return int
     */
    public static function getBildungsKompetenzsteine(GameEvents $stream, PlayerId $playerId): int
    {
        // TODO for current lebensziel phase only?
        $accumulatedResourceChangesForPlayer = $stream->findAllOfType(ProvidesResourceChanges::class)
            ->reduce(fn(ResourceChanges $accumulator, ProvidesResourceChanges $event) => $accumulator->accumulate($event->getResourceChanges($playerId)), new ResourceChanges());

        return $accumulatedResourceChangesForPlayer->bildungKompetenzsteinChange;
    }

    /**
     * Returns the accumulated amount of Freizeitkompetenzsteine of the player.
     *
     * @param GameEvents $stream
     * @param PlayerId $playerId
     * @return int
     */
    public static function getFreizeitKompetenzsteine(GameEvents $stream, PlayerId $playerId): int
    {
        // TODO for current lebensziel phase only?
        $accumulatedResourceChangesForPlayer = $stream->findAllOfType(ProvidesResourceChanges::class)
            ->reduce(fn(ResourceChanges $accumulator, ProvidesResourceChanges $event) => $accumulator->accumulate($event->getResourceChanges($playerId)), new ResourceChanges());

        return $accumulatedResourceChangesForPlayer->freizeitKompetenzsteinChange;
    }

    /**
     * Returns the total number of zeitsteine placed by the player in the specified category during the current Konjunkturphase.
     *
     * @param GameEvents $stream The collection of game events to be analyzed.
     * @param PlayerId $playerId The ID of the player for whom the calculation is performed.
     * @param CategoryId $category The category in which the zeitsteine placement is being calculated.
     * @return int The total number of zeitsteine placed by the player in the specified category during the current Konjunkturphase.
     */
    public static function getZeitsteinePlacedForCurrentKonjunkturphaseInCategory(GameEvents $stream, PlayerId $playerId, CategoryId $category): int
    {
        $zeitsteinAktionenForPlayerAndBildung = $stream->findAllAfterLastOfType(KonjunkturphaseWasChanged::class)->findAllOfType(ZeitsteinAktion::class)
            ->filter(fn(ZeitsteinAktion $event) => $event->getCategoryId() === $category && $event->getPlayerId()->equals($playerId));

        $sum = 0;
        /** @var ZeitsteinAktion $event */
        foreach ($zeitsteinAktionenForPlayerAndBildung as $event) {
            $sum += $event->getNumberOfZeitsteinslotsUsed();
        }

        return $sum;
    }

    /**
     * Returns the JobCardDefinition of the currently active job for the specified player.
     *
     * @param GameEvents $gameEvents The collection of game events to be analyzed.
     * @param PlayerId $playerId The ID of the player
     * @return JobCardDefinition|null The last job accepted by the player, or null if none exists.
     */
    public static function getJobForPlayer(GameEvents $gameEvents, PlayerId $playerId): ?JobCardDefinition
    {
        // Find all events AFTER the player accepted the last job (returns null, if player never accepted a job)
        $eventsAfterJobWasAccepted = $gameEvents->findAllAfterLastOrNullWhere(
            fn ($event) => $event instanceof JobOfferWasAccepted && $event->playerId->equals($playerId));

        // player never accepted a job
        if ($eventsAfterJobWasAccepted === null) {
            return null;
        }

        $jobWasQuitEvent = $eventsAfterJobWasAccepted->findLastOrNullWhere(fn ($event) => $event instanceof JobWasQuit && $event->playerId->equals($playerId));

        // player quit the last job
        if ($jobWasQuitEvent !== null) {
            return null;
        }

        /** @var  JobOfferWasAccepted $jobOfferWasAcceptedEvent */
        $jobOfferWasAcceptedEvent = $gameEvents->findLastOrNullWhere(fn ($event) => $event instanceof JobOfferWasAccepted && $event->playerId->equals($playerId));
        // Otherwise, return the active job definition
        /** @var JobCardDefinition $jobDefinition */
        $jobDefinition = CardFinder::getInstance()->getCardById($jobOfferWasAcceptedEvent->cardId);
        return $jobDefinition;
    }

    public static function getLastMinijobForPlayer(GameEvents $stream, PlayerId $playerId): ?MinijobCardDefinition
    {
        /**@var MinijobWasDone|null $minijobWasDoneEvent */
        $minijobWasDoneEvent = $stream->findLastOrNullWhere(fn($e) => $e instanceof MinijobWasDone && $e->playerId->equals($playerId));
        if ($minijobWasDoneEvent === null) {
            return null;
        }

        // @phpstan-ignore property.notFound (At this point we know this is an instance of MinijobWasDone and not null)
        $cardId = $minijobWasDoneEvent->minijobCardId;

        /** @var MinijobCardDefinition $minijobDefinition */
        $minijobDefinition = CardFinder::getInstance()->getCardById($cardId);
        return $minijobDefinition;
    }

    /**
     * @param GameEvents $stream
     * @param PlayerId $playerId
     * @return MoneyAmount
     */
    public static function getGehaltForPlayer(GameEvents $stream, PlayerId $playerId): MoneyAmount
    {
        // TODO modifier berücksichtigen
        $job = self::getJobForPlayer($stream, $playerId);
        return $job->gehalt ?? new MoneyAmount(0);
    }
}
