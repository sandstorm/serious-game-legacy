<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior;

use Domain\CoreGameLogic\Dto\ValueObject\ModifierCollection;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Dto\ValueObject\ResourceChangeCollection;
use Domain\CoreGameLogic\Dto\ValueObject\ResourceChanges;

/**
 * This interface is usually applied on GameEvents which also provide resource modifications.
 *
 * Eine Ressource steht für zukünftige Spielhandlungen zur Verfügung (e.g. Zeitsteine, Guthaben, …)
 */
interface ProvidesResourceChanges
{
    public function getResourceChanges(PlayerId $playerId): ResourceChanges;
}
