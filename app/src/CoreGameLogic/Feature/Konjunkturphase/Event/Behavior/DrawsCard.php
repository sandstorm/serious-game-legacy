<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Konjunkturphase\Event\Behavior;

use Domain\Definitions\Card\ValueObject\PileId;

/**
 * Use this interface for Events that draw a card. It will be used to track which/how many cards have
 * been drawn from a pile and to determine the card that is currently on top of the pile.
 */
interface DrawsCard
{
    public function getPileId(): PileId;
}
