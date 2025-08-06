<?php
declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\State;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\Feature\Initialization\Event\GameWasStarted;
use Domain\CoreGameLogic\Feature\Initialization\Event\LebenszielWasSelected;
use Domain\CoreGameLogic\Feature\Initialization\Event\NameForPlayerWasSet;
use Domain\CoreGameLogic\Feature\Initialization\Event\PreGameStarted;
use Domain\CoreGameLogic\Feature\Initialization\State\GamePhaseState;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior\ProvidesStockAmountChanges;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\StockPriceState;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\StockAmountChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Event\AnswerForWeiterbildungWasSubmitted;
use Domain\CoreGameLogic\Feature\Spielzug\Event\StocksWereNotSoldForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Event\StocksWereSoldForPlayer;
use Domain\CoreGameLogic\Feature\Spielzug\Event\WeiterbildungWasStarted;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LebenszielphaseWasChanged;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobWasQuit;
use Domain\CoreGameLogic\Feature\Spielzug\Event\MinijobWasDone;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\StockType;
use Domain\Definitions\Card\Dto\WeiterbildungCardDefinition;
use Domain\Definitions\Card\Dto\MinijobCardDefinition;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ZeitsteinAktion;
use Domain\CoreGameLogic\Feature\Spielzug\Event\JobOfferWasAccepted;
use Domain\CoreGameLogic\Feature\Spielzug\Event\SpielzugWasEnded;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\HookEnum;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\PlayerTurn;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\CardFinder;
use Domain\Definitions\Card\Dto\JobCardDefinition;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Konjunkturphase\ValueObject\AuswirkungScopeEnum;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryId;
use Domain\Definitions\Lebensziel\Dto\LebenszielDefinition;
use Domain\Definitions\Lebensziel\Dto\LebenszielPhaseDefinition;
use RuntimeException;

class PlayerState
{
    /**
     * @param GameEvents $stream
     * @param PlayerId $playerId
     * @return string
     */
    public static function getPlayerColorClass(GameEvents $stream, PlayerId $playerId): string
    {
        $playerOrder = GamePhaseState::getOrderedPlayers($stream);

        foreach ($playerOrder as $index => $playerIdFromOrder) {
            if ($playerIdFromOrder->equals($playerId)) {
                // return the color class for the player
                return "player-color-" . $index + 1;
            }
        }

        // If playerId is not found in the player ordering, throw an exception
        throw new RuntimeException('Player ' . $playerId . ' not found in player ordering', 1752835827);
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
    public static function getZeitsteineForPlayer(GameEvents $gameEvents, PlayerId $playerId): int
    {
        if (!in_array(needle: $playerId, haystack: $gameEvents->findLast(PreGameStarted::class)->playerIds, strict: true)) {
            throw new RuntimeException('Player ' . $playerId . ' does not exist', 1748432811);
        }
        $accumulatedResourceChangesForPlayer = $gameEvents->findAllAfterLastOfType(KonjunkturphaseWasChanged::class)->findAllOfType(ProvidesResourceChanges::class)
            ->reduce(fn(ResourceChanges $accumulator, ProvidesResourceChanges $event) => $accumulator->accumulate($event->getResourceChanges($playerId)), new ResourceChanges());

        $zeitsteineForPlayer = $accumulatedResourceChangesForPlayer->accumulate(new ResourceChanges(zeitsteineChange: + KonjunkturphaseState::getInitialZeitsteineForCurrentKonjunkturphase($gameEvents)))->zeitsteineChange;
        return ModifierCalculator::forStream($gameEvents)->forPlayer($playerId)->modify($gameEvents, HookEnum::ZEITSTEINE, $zeitsteineForPlayer);
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
     * Returns the accumulated amount of Bildungskompetenzsteine of the player for current Lebenszielphase.
     *
     * @param GameEvents $stream
     * @param PlayerId $playerId
     * @return float
     */
    public static function getBildungsKompetenzsteine(GameEvents $stream, PlayerId $playerId): float
    {
        $lebenszielPhaseWasChanged = $stream->findLastOrNull(LebenszielphaseWasChanged::class);
        $findAfterEvent = LebenszielphaseWasChanged::class;
        if ($lebenszielPhaseWasChanged === null) {
            $findAfterEvent = GameWasStarted::class;
        }

        $accumulatedResourceChangesForPlayer = $stream->findAllAfterLastOfType($findAfterEvent)->findAllOfType(ProvidesResourceChanges::class)
            ->reduce(fn(ResourceChanges $accumulator, ProvidesResourceChanges $event) => $accumulator->accumulate($event->getResourceChanges($playerId)), new ResourceChanges());

        return $accumulatedResourceChangesForPlayer->bildungKompetenzsteinChange;
    }

    /**
     * Returns the accumulated amount of Freizeitkompetenzsteine of the player for current Lebenszielphase.
     *
     * @param GameEvents $stream
     * @param PlayerId $playerId
     * @return int
     */
    public static function getFreizeitKompetenzsteine(GameEvents $stream, PlayerId $playerId): int
    {
        $lebenszielPhaseWasChanged = $stream->findLastOrNull(LebenszielphaseWasChanged::class);
        $findAfterEvent = LebenszielphaseWasChanged::class;
        if ($lebenszielPhaseWasChanged === null) {
            $findAfterEvent = GameWasStarted::class;
        }

        $accumulatedResourceChangesForPlayer = $stream->findAllAfterLastOfType($findAfterEvent)->findAllOfType(ProvidesResourceChanges::class)
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
        $jobDefinition = CardFinder::getInstance()->getCardById($jobOfferWasAcceptedEvent->cardId, JobCardDefinition::class);
        return $jobDefinition;
    }

    /**
     * @param GameEvents $stream
     * @param PlayerId $playerId
     * @return MinijobCardDefinition|null
     */
    public static function getLastMinijobForPlayer(GameEvents $stream, PlayerId $playerId): ?MinijobCardDefinition
    {
        /** @var MinijobWasDone|null $minijobWasDoneEvent */
        $minijobWasDoneEvent = $stream->findLastOrNullWhere(fn($e) => $e instanceof MinijobWasDone && $e->playerId->equals($playerId));
        if ($minijobWasDoneEvent === null) {
            return null;
        }

        return CardFinder::getInstance()->getCardById($minijobWasDoneEvent->minijobCardId, MinijobCardDefinition::class);
    }

    /**
     * @param GameEvents $stream
     * @param PlayerId $playerId
     * @return WeiterbildungCardDefinition|null
     */
    public static function getLastWeiterbildungCardDefinitionForPlayer(GameEvents $stream, PlayerId $playerId): ?WeiterbildungCardDefinition
    {
        $weiterbildungEvent = self::getLastWeiterbildungsEventForPlayerThisTurn($stream, $playerId);
        if ($weiterbildungEvent === null) {
            return null;
        }

        return CardFinder::getInstance()->getCardById($weiterbildungEvent->weiterbildungCardId, WeiterbildungCardDefinition::class);
    }

    /**
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return WeiterbildungWasStarted|null
     */
    public static function getLastWeiterbildungsEventForPlayerThisTurn(GameEvents $gameEvents, PlayerId $playerId): ?WeiterbildungWasStarted
    {
        $eventsThisTurn = $gameEvents->findAllAfterLastOfTypeOrNull(SpielzugWasEnded::class)
            ?? $gameEvents->findAllAfterLastOfType(GameWasStarted::class);

        /** @var WeiterbildungWasStarted|null $weiterbildungEvent */
        $weiterbildungEvent = $eventsThisTurn->findLastOrNullWhere(
            fn($e) => $e instanceof WeiterbildungWasStarted && $e->playerId->equals($playerId)
        );
        return $weiterbildungEvent;
    }

    /**
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return AnswerForWeiterbildungWasSubmitted|null
     */
    public static function getSubmittedAnswerForLatestWeiterbildungThisTurn(GameEvents $gameEvents, PlayerId $playerId): ?AnswerForWeiterbildungWasSubmitted
    {
        $eventsThisTurn = $gameEvents->findAllAfterLastOfTypeOrNull(SpielzugWasEnded::class)
            ?? $gameEvents->findAllAfterLastOfType(GameWasStarted::class);

        /** @var AnswerForWeiterbildungWasSubmitted|null $submittedAnswerEvent */
        $submittedAnswerEvent = $eventsThisTurn->findLastOrNullWhere(
            fn($e) => $e instanceof AnswerForWeiterbildungWasSubmitted
                && $e->playerId->equals($playerId)
        );
        return $submittedAnswerEvent;
    }

    /**
     * @param GameEvents $stream
     * @param PlayerId $playerId
     * @return MoneyAmount
     */
    public static function getCurrentGehaltForPlayer(GameEvents $stream, PlayerId $playerId): MoneyAmount
    {
        $job = self::getJobForPlayer($stream, $playerId);
        $gehalt = $job?->getGehalt() ?? new MoneyAmount(0);
        return ModifierCalculator::forStream($stream)->forPlayer($playerId)->modify($stream, HookEnum::GEHALT, $gehalt);
    }

    /**
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return PlayerTurn the current turn number for the player (starting at 1)
     */
    public static function getCurrentTurnForPlayer(GameEvents $gameEvents, PlayerId $playerId): PlayerTurn
    {
        $spielzugWasEndedEvents = $gameEvents->findAllOfType(SpielzugWasEnded::class)
            ->filter(fn (SpielzugWasEnded $event) => $event->playerId->equals($playerId));
        if ($spielzugWasEndedEvents === null) {
            return new PlayerTurn(1);
        }
        return new PlayerTurn(count($spielzugWasEndedEvents) + 1);
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

    public static function getCurrentLebenszielphaseIdForPlayer(GameEvents $gameEvents, PlayerId $playerId): LebenszielPhaseId
    {
        /** @var LebenszielphaseWasChanged|null $lastLebenszielWasChangedEvent */
        $lastLebenszielWasChangedEvent = $gameEvents->findLastOrNullWhere(fn ($event) => $event instanceof LebenszielphaseWasChanged && $event->playerId->equals($playerId));
        if($lastLebenszielWasChangedEvent === null) { // We know we are in Lebenszielphase 1
            return LebenszielPhaseId::PHASE_1;
        }
        return $lastLebenszielWasChangedEvent->currentPhase;
    }


    public static function getCurrentLebenszielphaseDefinitionForPlayer(GameEvents $gameEvents, PlayerId $playerId): LebenszielPhaseDefinition
    {
        $lebenszielDefinition = self::getLebenszielDefinitionForPlayer($gameEvents, $playerId);

        /** @var LebenszielphaseWasChanged|null $lastLebenszielWasChangedEvent */
        $lastLebenszielWasChangedEvent = $gameEvents->findLastOrNullWhere(fn ($event) => $event instanceof LebenszielphaseWasChanged && $event->playerId->equals($playerId));
        if($lastLebenszielWasChangedEvent === null) { // We know we are in Lebenszielphase 1
            return $lebenszielDefinition->phaseDefinitions[0];
        }
        return $lebenszielDefinition->phaseDefinitions[$lastLebenszielWasChangedEvent->currentPhase->value - 1];
    }

    /**
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return string
     */
    public static function getNameForPlayer(GameEvents $gameEvents, PlayerId $playerId): string
    {
        $name = self::getNameForPlayerOrNull($gameEvents, $playerId);
        if ($name === null) {
            throw new \RuntimeException('No Player Name found', 1753088654);
        }

        return $name;
    }

    /**
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return string|null
     */
    public static function getNameForPlayerOrNull(GameEvents $gameEvents, PlayerId $playerId): ?string
    {
        // @phpstan-ignore property.notFound
        return $gameEvents->findLastOrNullWhere(fn($e) => $e instanceof NameForPlayerWasSet && $e->playerId->equals($playerId))?->name;
    }

    /**
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return LebenszielDefinition
     */
    public static function getLebenszielDefinitionForPlayer(GameEvents $gameEvents, PlayerId $playerId): LebenszielDefinition
    {
        $lebensziel = self::getLebenszielDefinitionForPlayerOrNull($gameEvents, $playerId);
        if ($lebensziel === null) {
            throw new \RuntimeException('No Lebensziel found', 1753088724);
        }

        return $lebensziel;
    }

    /**
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return LebenszielDefinition|null
     */
    public static function getLebenszielDefinitionForPlayerOrNull(GameEvents $gameEvents, PlayerId $playerId): ?LebenszielDefinition
    {
        // @phpstan-ignore property.notFound
        return $gameEvents->findLastOrNullWhere(fn($e) => $e instanceof LebenszielWasSelected && $e->playerId->equals($playerId))?->lebenszielDefinition;
    }

    /**
     * @param GameEvents $stream
     * @param PlayerId $playerId
     * @return MoneyAmount
     */
    public static function getTotalValueOfAllAssetsForPlayer(GameEvents $stream, PlayerId $playerId): MoneyAmount
    {
        $stocksValue = self::getTotalValueOfAllStocksForPlayer($stream, $playerId);
        // TODO immobilien, etc

        return $stocksValue;
    }

    /**
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @return bool
     */
    public static function hasPlayerInteractedWithStocksModalThisTurn(GameEvents $gameEvents, PlayerId $playerId): bool
    {
        $eventsThisTurn = $gameEvents->findAllAfterLastOfTypeOrNull(SpielzugWasEnded::class)
            ?? $gameEvents->findAllAfterLastOfType(GameWasStarted::class);

        // Check if the player has sold stocks this turn or decided not to sell stocks this turn.
        $stocksWereSold = $eventsThisTurn->findLastOrNullWhere(fn ($event) => $event instanceof StocksWereSoldForPlayer && $event->playerId->equals($playerId));
        $stocksWereNotSold = $eventsThisTurn->findLastOrNullWhere(fn ($event) => $event instanceof StocksWereNotSoldForPlayer && $event->playerId->equals($playerId));

        return $stocksWereSold !== null || $stocksWereNotSold !== null;
    }
}
