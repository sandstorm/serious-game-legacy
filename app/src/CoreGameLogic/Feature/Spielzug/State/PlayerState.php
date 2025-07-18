<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\PlayerColorWasSelected;
use Domain\CoreGameLogic\Feature\Initialization\Event\PreGameStarted;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\ProvidesStockAmountChanges;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\StockPriceState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\StockAmountChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LebenszielphaseWasChanged;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobWasQuit;
use Domain\CoreGameLogic\Feature\Spielzug\Event\MinijobWasDone;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;
use Domain\Definitions\Card\Dto\MinijobCardDefinition;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ZeitsteinAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobOfferWasAccepted;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Konjunkturphase\ValueObject\AuswirkungScopeEnum;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Domain\Definitions\Lebensziel\Dto\LebenszielPhaseDefinition;
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

        $zeitsteineForPlayer = $accumulatedResourceChangesForPlayer->accumulate(new ResourceChanges(zeitsteineChange: + KonjunkturphaseState::getInitialZeitsteineForCurrentKonjunkturphase($stream)))->zeitsteineChange;
        return ModifierCalculator::forStream($stream)->forPlayer($playerId)->applyToAvailableZeitsteine($zeitsteineForPlayer);
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

    /**
     * @param GameEvents $stream
     * @param PlayerId $playerId
     * @param StockType $stockType
     * @return int
     */
    public static function getAmountOfAllStocksOfTypeForPlayer(GameEvents $stream, PlayerId $playerId, StockType $stockType): int
    {
        $accumulatedStockAmountChangesForPlayer = $stream->findAllOfType(ProvidesStockAmountChanges::class)
            ->reduce(fn(StockAmountChanges $accumulator, ProvidesStockAmountChanges $event) => $accumulator->accumulate($event->getStockAmountChanges($playerId, $stockType)), new StockAmountChanges());

        return $accumulatedStockAmountChangesForPlayer->amountChange;
    }

    /**
     * Returns the total of all stocks for the specified player, across all stock types.
     *
     * @param GameEvents $stream
     * @param PlayerId $playerId
     * @return MoneyAmount
     */
    public static function getTotalValueOfAllStocksForPlayer(GameEvents $stream, PlayerId $playerId): MoneyAmount
    {
        $sum = new MoneyAmount(0);
        foreach (StockType::cases() as $stockType) {
            $amountOfStocks = self::getAmountOfAllStocksOfTypeForPlayer($stream, $playerId, $stockType);
            $currentPrice = StockPriceState::getCurrentStockPrice($stream, $stockType);

            $sum = $sum->add(new MoneyAmount($amountOfStocks * $currentPrice->value));
        }
        return $sum;
    }

    /**
     * Returns the total of all dividends for all stocks of type LOW_RISK for the specified player.
     *
     * @param GameEvents $stream
     * @param PlayerId $playerId
     * @return MoneyAmount
     */
    public static function getDividendForAllStocksForPlayer(GameEvents $stream, PlayerId $playerId): MoneyAmount
    {
        $currentKonjunkturphase = KonjunkturphaseState::getCurrentKonjunkturphase($stream);
        $currentDividend = $currentKonjunkturphase->getAuswirkungByScope(AuswirkungScopeEnum::DIVIDEND)->modifier;
        $amountOfLowRiskStocksForPlayer = self::getAmountOfAllStocksOfTypeForPlayer($stream, $playerId, StockType::LOW_RISK);


        return new MoneyAmount($currentDividend * $amountOfLowRiskStocksForPlayer);
    }

    public static function getCurrentLebenszielphaseDefinitionForPlayer(GameEvents $gameEvents, PlayerId $playerId): LebenszielPhaseDefinition
    {
        $lebenszielDefinition = PreGameState::lebenszielDefinitionForPlayer($gameEvents, $playerId);

        /** @var LebenszielphaseWasChanged|null $lastLebenszielWasChangedEvent */
        $lastLebenszielWasChangedEvent= $gameEvents->findLastOrNullWhere(fn ($event) => $event instanceof LebenszielphaseWasChanged && $event->playerId->equals($playerId));
        if($lastLebenszielWasChangedEvent === null) { // We know we are in Lebenszielphase 1
            return $lebenszielDefinition->phaseDefinitions[0];
        }
        return $lebenszielDefinition->phaseDefinitions[$lastLebenszielWasChangedEvent->currentPhase - 1];
    }
}
