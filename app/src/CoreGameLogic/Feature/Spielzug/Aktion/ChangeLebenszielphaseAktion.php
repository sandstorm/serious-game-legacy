<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\HasPlayerEnoughResourcesForLebenszielphasenChangeValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Aktion\Validator\IsPlayersTurnValidator;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LebenszielphaseWasChanged;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use RuntimeException;

class ChangeLebenszielphaseAktion extends Aktion
{
    public function __construct()
    {
        parent::__construct('change-lebensziel', 'Phase wechseln');
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $validatorChain = new IsPlayersTurnValidator();
        $validatorChain->setNext(new HasPlayerEnoughResourcesForLebenszielphasenChangeValidator());

        return $validatorChain->validate($gameEvents, $playerId);
    }

    // Moves to the next Lebenszielphase, changes the Ressources which are nessesary for the next phase and creating the event
    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new RuntimeException('Cannot Change Lebensphase: ' . $result->reason, 1751619852);
        }
        $currentPhaseDefinition = PlayerState::getCurrentLebenszielphaseDefinitionForPlayer($gameEvents, $playerId);
        $resourceChanges = new ResourceChanges(
            guthabenChange: new MoneyAmount($currentPhaseDefinition->investitionen->value * -1),
            bildungKompetenzsteinChange: -1 * PlayerState::getBildungsKompetenzsteine($gameEvents, $playerId),
            freizeitKompetenzsteinChange: -1 * PlayerState::getFreizeitKompetenzsteine($gameEvents, $playerId),
        );

        return GameEventsToPersist::with(
            new LebenszielphaseWasChanged($playerId, $resourceChanges, LebenszielPhaseId::from($currentPhaseDefinition->lebenszielPhaseId->value + 1))
        );
    }
}

