<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\PlayerId;

abstract class Aktion
{
    public function __construct(protected string $id, protected string $label)
    {
    }

    /**
     * Preconditions - kann Aktion aktuell ausgeführt werden?
     * // TODO: vmtl. kein Bool Result, sondern "Result" objekt mit Zustand und Begründung
     *
     * @return bool
     */
    abstract public function canExecute(PlayerId $player, GameEvents $eventStream): bool;

    abstract public function execute(PlayerId $player, GameEvents $eventStream): GameEventsToPersist;
}
