<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Aktion;

use Domain\CoreGameLogic\EventStore\GameEvents;
use Domain\CoreGameLogic\EventStore\GameEventsToPersist;
use Domain\CoreGameLogic\Feature\Spielzug\Dto\AktionValidationResult;
use Domain\CoreGameLogic\PlayerId;

abstract class Aktion
{
    // TODO remove id and label
    public function __construct(protected string $id, protected string $label)
    {
    }

    /**
     * Preconditions - kann Aktion aktuell ausgeführt werden?
     */
    abstract public function validate(PlayerId $playerId, GameEvents $gameEvents): AktionValidationResult;

    abstract public function execute(PlayerId $playerId, GameEvents $gameEvents): GameEventsToPersist;
}
