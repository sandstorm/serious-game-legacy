<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior;


use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Konjunkturphase\ValueObject\CategoryEnum;

/**
 * This interface is applied on GameEvents which use a Zeitstein. Usually a player can only perform one of those
 * actions per turn and can only finish their turn after performing such an action (unless the player has 0 Zeitsteine).
 *
 * An exception is the combination of SkipCard and ActivateCard, where the player can activate the Card after the skipped Card.
 */
interface ZeitsteinAktion
{
    public function getCategory(): CategoryEnum; // e.g. 'BILDUNG' or 'FREIZEIT'
    public function getPlayerId(): PlayerId;
}
