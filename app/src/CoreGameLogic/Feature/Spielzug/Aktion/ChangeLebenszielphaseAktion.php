<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Initialization\Event\LebenszielWasSelected;
use Domain\CoreGameLogic\Feature\Initialization\State\PreGameState;
use Domain\CoreGameLogic\Feature\Initialization\ValueObject\Lebensziel;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\Event\LebenszielphaseWasChanged;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\CoreGameLogic\Feature\Spielzug\State\PlayerState;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Lebensziel\LebenszielFinder;

class ChangeLebenszielphaseAktion extends Aktion
{
    public function __construct()
    {
        parent::__construct('change-lebensziel', 'Phase wechseln');
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
        $currentPlayer = CurrentPlayerAccessor::forStream($gameEvents);
        if (!$currentPlayer->equals($playerId)) {
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du kannst die Phase nur wechseln, wenn du dran bist'
            );
        }

        // bekommen aktuelles Lebensziel für Player
        $currentLebensziel = PreGameState::lebenszielForPlayerOrNull($gameEvents, $playerId);
        // bekommen aktuelle Phase des Lebensziels
        $currentLebenszielPhase = $currentLebensziel->phaseDefinitions[0]; // TODO use currentPhaseForPlayer - 1
        // Ressourcen der aktuellen Phase
        $phaseResources = [
            'bildungsKompetenzSteine' => $currentLebenszielPhase->bildungsKompetenzSlots,
            'freizeitKompetenzSteine' => $currentLebenszielPhase->freizeitKompetenzSlots,
            'erwerbseinkommen' => $currentLebenszielPhase?->erwerbseinkommen,
        ];

        // Resourcen des Players
        $playerResources =  PlayerState::getResourcesForPlayer($gameEvents, $playerId);
        // Menge prüfen, für jede Resource in der Phase
        foreach ($phaseResources as $resourceName => $requiredAmount) {
            $playerAmount = $playerResources[$resourceName];

            if ($playerAmount < $requiredAmount) {
                return new AktionValidationResult(
                    canExecute: false, reason: "Du kannst die Phase nur wechseln, wenn du genug $resourceName hast (benötigt: $requiredAmount, hast: $playerAmount)"
                );
            }
            return new AktionValidationResult(true);
        }
    }

    // TODO:
    //$kontostand = KontostandAccessor::forPlayer($player, $eventStream);
    //$aktuellePhaseDesSpielers = AktuellePhaseAccessor::forPlayer($player, $eventStream);
    // TODO: für aktuelle Phase rausfinden was die Abschlussbedingungen sind - TODO: CONFIG / HARDCODED / LEBENSZIEL

    // TODO: entscheidung

    public
    function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        $result = $this->validate($playerId, $gameEvents);
        if (!$result->canExecute) {
            throw new RuntimeException('Cannot Change Lebensphase: ' . $result->reason, 1751619852);
        }
        return GameEventsToPersist::with(
            new LebenszielphaseWasChanged($playerId)
        );
    }
}

