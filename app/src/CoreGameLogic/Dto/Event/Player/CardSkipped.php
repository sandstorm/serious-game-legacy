<?php
declare(strict_types=1);
namespace Domain\CoreGameLogic\Dto\Event\Player;

use Domain\CoreGameLogic\Dto\ValueObject\CardId;
use Domain\CoreGameLogic\Dto\ValueObject\CurrentYear;
use Domain\CoreGameLogic\Dto\ValueObject\Leitzins;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;

readonly final class CardSkipped
{


    public function __construct(
        public PlayerId $player, public CardId $card,
    )
    {
    }
}
