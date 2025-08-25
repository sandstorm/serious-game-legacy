<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\DoesNotSkipTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerEnoughResourcesForLebenszielphasenChangeValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayersTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\PlayerHasFinishedTheGame;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use RuntimeException;

class FinishGameAktion extends Aktion
{
    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validatorChain = new IsPlayersTurnValidator();
        $validatorChain
            ->setNext(new DoesNotSkipTurnValidator())
            ->setNext(new HasPlayerEnoughResourcesForLebenszielphasenChangeValidator());

        return $validatorChain->validate($gameEvents, $playerId);
    }

    // Finishes the game for the player, changes the resources which are necessary to finish the phase and creating the event
    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new RuntimeException('Cannot Finish Game: ' . $result->reason, 1751619852);
        }
        $currentPhaseDefinition = PlayerState::getCurrentLebenszielphaseDefinitionForPlayer($gameEvents, $playerId);
        $retainedKompetenzsteineInBildungUndKarriere = $currentPhaseDefinition->bildungsKompetenzSlots;
        $retainedKompetenzsteineInSozialesUndFreizeit = $currentPhaseDefinition->freizeitKompetenzSlots;
        $resourceChanges = new ResourceChanges(
            guthabenChange: new MoneyAmount($currentPhaseDefinition->investitionen->value * -1),
            bildungKompetenzsteinChange: -1 * PlayerState::getBildungsKompetenzsteine($gameEvents, $playerId) + $retainedKompetenzsteineInBildungUndKarriere,
            freizeitKompetenzsteinChange: -1 * PlayerState::getFreizeitKompetenzsteine($gameEvents, $playerId) + $retainedKompetenzsteineInSozialesUndFreizeit,
        );

        return GameEventsToPersist::with(
            new PlayerHasFinishedTheGame($playerId, $resourceChanges)
        );
    }
}

