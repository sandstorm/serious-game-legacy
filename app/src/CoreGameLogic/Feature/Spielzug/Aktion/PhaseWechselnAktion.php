<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\PlayerId;

class PhaseWechselnAktion extends Aktion
{
    public function __construct()
    {
        parent::__construct('phase-wechseln', 'Phase wechseln');
    }

    public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult
    {
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
