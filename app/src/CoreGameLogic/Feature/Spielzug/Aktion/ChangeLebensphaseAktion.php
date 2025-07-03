<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\Feature\Spielzug\State\CurrentPlayerAccessor;
use Domain\CoreGameLogic\PlayerId;

class ChangeLebensphaseAktion extends Aktion
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

        if () {
            // herausfinden wie viele Bildungskompetenzsteine, Freizeitkompetenzsteine für Phasenziel benötigt werden
            // schauen ob Anzahl für Lebensphase erreicht (Events abfragen, ob jeweilige Events ausgelöst wurden, zählen)
            // Kontostand abfragen
            // getBildungsKompetenzsteine, getFreizeitKompetenzsteine PlayerState
            //
            return new AktionValidationResult(
                canExecute: false,
                reason: 'Du kannst die Phase nur wechseln, wenn du das Phasenziel erreicht hast'
            );
        }

        // TODO:
        //$kontostand = KontostandAccessor::forPlayer($player, $eventStream);
        //$aktuellePhaseDesSpielers = AktuellePhaseAccessor::forPlayer($player, $eventStream);
        // TODO: für aktuelle Phase rausfinden was die Abschlussbedingungen sind - TODO: CONFIG / HARDCODED / LEBENSZIEL

        // TODO: entscheidung

        return new AktionValidationResult(true);
    }

    public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist
    {
        // TODO: Implement execute() method.
        return GameEventsToPersist::empty();
    }
}
