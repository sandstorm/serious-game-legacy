<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Dto\Aktion;

use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEvents;

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

    abstract public function execute(PlayerId $player, GameEvents $eventStream): GameEvents;
}
