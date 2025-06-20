<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesModifiers;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\CoreGameLogic\Feature\Spielzug\Modifier\Modifier;
use Domain\CoreGameLogic\Feature\Spielzug\Modifier\ModifierCollection;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\EreignisId;
use Domain\CoreGameLogic\Feature\Spielzug\ValueObject\ModifierId;
use Domain\CoreGameLogic\PlayerId;
use Domain\Definitions\Card\Dto\ResourceChanges;
use Domain\Definitions\Card\ValueObject\MoneyAmount;

final readonly class EreignisWasTriggered implements ProvidesModifiers, ProvidesResourceChanges, GameEventInterface
{
    public function __construct(
        public PlayerId $player,
        public EreignisId $ereignis,
    ) {
    }

    public function getModifiers(PlayerId $playerId): ModifierCollection
    {
        if ($this->ereignis->value === "EVENT:OmaKrank" && $this->player->equals($playerId)) {
            return new ModifierCollection([new Modifier(ModifierId::AUSSETZEN)]);
        }
        return new ModifierCollection([]);
    }


    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($this->ereignis->value === "EVENT:Lotteriegewinn" && $this->player->equals($playerId)) {
            return new ResourceChanges(guthabenChange: new MoneyAmount(1000), zeitsteineChange: 0);
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
