<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\Dto\ValueObject\EreignisId;
use Domain\CoreGameLogic\Dto\ValueObject\Modifier;
use Domain\CoreGameLogic\Dto\ValueObject\ModifierCollection;
use Domain\CoreGameLogic\Dto\ValueObject\ModifierId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Dto\ValueObject\ResourceChanges;
use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesModifiers;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;

final readonly class TriggeredEreignis implements ProvidesModifiers, ProvidesResourceChanges, GameEventInterface
{
    public function __construct(
        public PlayerId $player,
        public EreignisId $ereignis,
    ) {
    }

    public function getModifiers(PlayerId $playerId): ModifierCollection
    {
        if ($this->ereignis->value === "EVENT:OmaKrank" && $this->player->equals($playerId)) {
            return new ModifierCollection([new Modifier(new ModifierId("MODIFIER:ausetzen"))]);
        }
        return new ModifierCollection([]);
    }


    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($this->ereignis->value === "EVENT:Lotteriegewinn" && $this->player->equals($playerId)) {
            return new ResourceChanges(guthabenChange: 1000);
        }
        return new ResourceChanges();
    }


    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            player: PlayerId::fromString($values['player']),
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
