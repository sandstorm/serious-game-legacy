<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Moneysheet\State\MoneySheetState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasKonjunkturphaseNotStartedValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasStartedKonjunkturphase;
use Domain\CoreGameLogic\Feature\Spielzug\SpielzugCommandHandler;
use Domain\CoreGameLogic\Feature\Spielzug\State\EreignisPrerequisiteChecker;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Konjunkturphase\Dto\ConditionalResourceChange;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;

/**
 * This event is fired when the player has handled the dialogue at the start of each Konjunkturphase.
 * After this event is fired, the player can see the GameBoard again.
 */
class StartKonjunkturphaseForPlayerAktion extends Aktion
{
    /**
     * Use this to check if this Aktion can be executed.
     * `execute()` will also use this function to check before actually creating any events.
     * @param PlayerId $playerId
     * @param GameEvents $gameEvents
     * @return AktionValidationResult
     */
    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validatorChain = new HasKonjunkturphaseNotStartedValidator();
        return $validatorChain->validate($gameEvents, $playerId);
    }

    private function getResourceChangesForPlayer(GameEvents $gameEvents, PlayerId $playerId): ResourceChanges
    {
        $conditionalResourceChangesForCurrentKonjunkturphase = KonjunkturphaseFinder::findKonjunkturphaseById(
            $gameEvents->findLast(KonjunkturphaseWasChanged::class)->id)->getConditionalResourceChanges();

        $accumulatedResourceChanges = new ResourceChanges();
        foreach ($conditionalResourceChangesForCurrentKonjunkturphase as $conditionalResourceChange) {
            $isConditionMet = EreignisPrerequisiteChecker::forStream($gameEvents)
                ->hasPlayerPrerequisites($playerId, $conditionalResourceChange->prerequisite);

            $actualResourceChanges = $this->calculateActualResourceChangeFromConditionalResourceChanges(
                $gameEvents,
                $playerId,
                $conditionalResourceChange
            );
            if ($isConditionMet) {
                $accumulatedResourceChanges = $accumulatedResourceChanges->accumulate($actualResourceChanges);
            }
        }
        return $accumulatedResourceChanges;
    }

    /**
     * Calculates the actual ResourceChanges, since there are some special cases (ExtraZins, Lohnsonderzahlung,
     * Grundsteuer) that need the current state to determine the actual cost which we cannot do in the definition.
     *
     * This special case only applies for KonjunkturphaseChanges and we only need to worry about it here.
     * @param GameEvents $gameEvents
     * @param PlayerId $playerId
     * @param ConditionalResourceChange $conditionalResourceChange
     * @return ResourceChanges
     */
    private function calculateActualResourceChangeFromConditionalResourceChanges(
        GameEvents $gameEvents,
        PlayerId $playerId,
        ConditionalResourceChange $conditionalResourceChange
    ): ResourceChanges {
        if ($conditionalResourceChange->isExtraZins) {
            $extraZinsAmount = $conditionalResourceChange->resourceChanges->guthabenChange->value;
            $numberOfLoans = count(MoneySheetState::getLoansForPlayer($gameEvents, $playerId));
            return new ResourceChanges(guthabenChange: new MoneyAmount($extraZinsAmount * $numberOfLoans));
        }
        if ($conditionalResourceChange->isGrundsteuer) {
            $grundSteuerAmount = $conditionalResourceChange->resourceChanges->guthabenChange->value;
            $numberOfProperties = 0; // TODO use real number of Real Estate Properties once it's implemented
            return new ResourceChanges(guthabenChange: new MoneyAmount($grundSteuerAmount * $numberOfProperties));
        }
        if ($conditionalResourceChange->lohnsonderzahlungPercent !== null) {
            $currentGehaltForPlayer = PlayerState::getCurrentGehaltForPlayer($gameEvents, $playerId)->value;
            $multiplier = $conditionalResourceChange->lohnsonderzahlungPercent / 100;
            return new ResourceChanges(guthabenChange: new MoneyAmount($currentGehaltForPlayer * $multiplier));
        }
        return $conditionalResourceChange->resourceChanges;
    }

    /**
     * This is used to actually create the Events for this Aktion. Should only be used in the {@see SpielzugCommandHandler}.
     *
     * @param PlayerId $playerId
     * @param GameEvents $gameEvents
     * @return GameEventsToPersist
     *
     * @example
     * // in SpielzugCommandHandler
     * $aktion = new StartKonjunkturphaseForPlayerAktion();
     * return $aktion->execute(
     *      playerId: $command->playerId,
     *      gameEvents: $gameEvents,
     * );
     *
     * @internal this is only meant to be used inside the respective command handlers
     */
    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new \RuntimeException('Cannot start Konjunkturphase: ' . $result->reason, 1751373528);
        }

        return GameEventsToPersist::with(
            new PlayerHasStartedKonjunkturphase(
                playerId: $playerId,
                year: KonjunkturphaseState::getCurrentYear($gameEvents),
                resourceChanges: $this->getResourceChangesForPlayer($gameEvents, $playerId),
            ),
        );
    }
}
