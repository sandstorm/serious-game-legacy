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

    // TODO:
    //$kontostand = KontostandAccessor::forPlayer($player, $eventStream);
    //$aktuellePhaseDesSpielers = AktuellePhaseAccessor::forPlayer($player, $eventStream);
    // TODO: für aktuelle Phase rausfinden was die Abschlussbedingungen sind - TODO: CONFIG / HARDCODED / LEBENSZIEL

    // TODO: entscheidung

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new RuntimeException('Cannot Change Lebensphase: ' . $result->reason, 1751619852);
        }
        $currentPhase = PlayerState::getCurrentLebenszielphaseForPlayer($gameEvents, $playerId);
        $resourceChanges = PlayerState::getResourcesForPlayer($gameEvents, $playerId);
        return GameEventsToPersist::with(
            new LebenszielphaseWasChanged($playerId, $resourceChanges, $currentPhase)
        );
    }
}

