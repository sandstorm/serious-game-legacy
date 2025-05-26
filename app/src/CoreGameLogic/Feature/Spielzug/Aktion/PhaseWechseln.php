<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\PlayerId;

class PhaseWechseln extends Aktion
{
    public function __construct()
    {
        parent::__construct('phase-wechseln', 'Phase wechseln');
    }

    public function canExecute(PlayerId $player, GameEvents $eventStream): bool
    {
        // TODO:
        //$kontostand = KontostandAccessor::forPlayer($player, $eventStream);
        //$aktuellePhaseDesSpielers = AktuellePhaseAccessor::forPlayer($player, $eventStream);
        // TODO: für aktuelle Phase rausfinden was die Abschlussbedingungen sind - TODO: CONFIG / HARDCODED / LEBENSZIEL

        // TODO: entscheidung



        return false;
    }

    public function execute(PlayerId $player, GameEvents $eventStream): GameEventsToPersist
    {
        // TODO: Implement execute() method.
        return GameEventsToPersist::empty();
    }
}
