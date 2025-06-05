<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\PlayerColorWasSelected;
use Domain\CoreGameLogic\Feature\Initialization\Event\PreGameStarted;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ZeitsteinAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobOfferWasAccepted;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;

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
            throw new \RuntimeException('Player ' . $playerId . ' does not exist', 1748432811);
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
    public static function getGuthabenForPlayer(GameEvents $stream, PlayerId $playerId): int
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
            ->filter(fn(ZeitsteinAktion $event) => $event->getCategory() === $category && $event->getPlayerId()->equals($playerId));

        $sum = 0;
        foreach ($zeitsteinAktionenForPlayerAndBildung as $event) {
            $sum += $event->getResourceChanges($playerId)->zeitsteineChange * -1;
        }

        return $sum;
    }

    /**
     * Returns the last JobOfferWasAccepted event for the specified player.
     *
     * @param GameEvents $stream The collection of game events to be analyzed.
     * @param PlayerId $playerId The ID of the player for whom the job offer is being retrieved.
     * @return JobOfferWasAccepted|null The last job offer accepted by the player, or null if no such event exists.
     */
    public static function getJobForPlayer(GameEvents $stream, PlayerId $playerId): ?JobOfferWasAccepted
    {
        // @phpstan-ignore return.type
        return $stream->findLastOrNullWhere(fn($e) => $e instanceof JobOfferWasAccepted && $e->player->equals($playerId));
    }
}
