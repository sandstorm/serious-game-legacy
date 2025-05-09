<?php
declare(strict_types=1);
namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\Dto\Event\Player\ProvidesModifiers;
use Domain\CoreGameLogic\Dto\ValueObject\EreignisId;
use Domain\CoreGameLogic\Dto\ValueObject\Modifier;
use Domain\CoreGameLogic\Dto\ValueObject\ModifierCollection;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\EventStore\GameEventInterface;

readonly final class TriggeredEreignis implements ProvidesModifiers, GameEventInterface
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


    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            player: new PlayerId($values['player']),
            ereignis: new EreignisId($values['ereignis']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->player,
            'ereignis' => $this->ereignis,
        ];
    }
}
