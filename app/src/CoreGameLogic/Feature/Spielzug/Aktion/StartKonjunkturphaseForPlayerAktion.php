<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Konjunkturphase\Event\KonjunkturphaseWasChanged;
use Domain\CoreGameLogic\Feature\Konjunkturphase\State\KonjunkturphaseState;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasKonjunkturphaseNotStartedValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasStartedKonjunkturphase;
use Domain\CoreGameLogic\Feature\Spielzug\SpielzugCommandHandler;
use Domain\CoreGameLogic\Feature\Spielzug\State\EreignisPrerequisiteChecker;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Konjunkturphase\KonjunkturphaseFinder;

/**
 * This event is fired when the player has handled the dialogue at the start of each Konjunkturphase.
 * After this event is fired, the player can see the GameBoard again.
 */
class StartKonjunkturphaseForPlayerAktion extends Aktion
{
    public function __construct()
    {
        parent::__construct('start-konjunkturphase-for-player', 'Konjunkturphase starten');
    }

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

        $resourceChanges = new ResourceChanges();
        foreach ($conditionalResourceChangesForCurrentKonjunkturphase as $conditionalResourceChange) {
            $isConditionMet = EreignisPrerequisiteChecker::forStream($gameEvents)
                ->hasPlayerPrerequisites($playerId, $conditionalResourceChange->prerequisite);

            if ($isConditionMet) {
                $resourceChanges = $resourceChanges->accumulate($conditionalResourceChange->resourceChanges);
            }
        }
        return $resourceChanges;
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
