<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior;

use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;

/**
 * This interface is usually applied on GameEvents which also provide resource modifications.
 *
 * Eine Ressource steht für zukünftige Spielhandlungen zur Verfügung (e.g. Zeitsteine, Guthaben, …)
 */
interface ProvidesResourceChanges
{
    public function getResourceChanges(PlayerId $playerId): ResourceChanges;
}
