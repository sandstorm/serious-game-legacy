<?php
declare(strict_types=1);
namespace Domain\CoreGameLogic\Dto\Event\Player;

use Domain\CoreGameLogic\Dto\ValueObject\CardId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;

readonly final class CardActivated
{

    public function __construct(
        public PlayerId $player,
        public CardId $card,
    )
    {
    }
}
