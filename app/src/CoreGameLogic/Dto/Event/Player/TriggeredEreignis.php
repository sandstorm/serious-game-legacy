<?php
declare(strict_types=1);
namespace Domain\CoreGameLogic\Dto\Event\Player;

use Domain\CoreGameLogic\Dto\ValueObject\EreignisId;
use Domain\CoreGameLogic\Dto\ValueObject\Modifier;
use Domain\CoreGameLogic\Dto\ValueObject\ModifierCollection;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;

readonly final class TriggeredEreignis implements ProvidesModifiers
{


    public function __construct(
        public PlayerId $player, public EreignisId $ereignis,
    )
    {
    }

    public function getModifiers(PlayerId $playerId): ModifierCollection
    {
        if ($this->ereignis->value === "EVENT:OmaKrank" && $this->player->equals($playerId)) {
            return new ModifierCollection([new Modifier("MODIFIER:ausetzen")]);
        }
        return new ModifierCollection([]);
    }
}
