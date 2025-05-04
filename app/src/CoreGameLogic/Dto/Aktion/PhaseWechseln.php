<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\Aktion;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;

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

    public function execute(PlayerId $player, GameEvents $eventStream): GameEvents
    {
        // TODO: Implement execute() method.
    }
}
