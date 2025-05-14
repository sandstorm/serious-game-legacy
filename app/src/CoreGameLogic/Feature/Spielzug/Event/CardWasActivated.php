<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\Dto\ValueObject\ModifierCollection;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Dto\ValueObject\ResourceChanges;
use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesModifiers;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;
use Domain\Definitions\Cards\Model\CardDefinition;

final readonly class CardWasActivated implements ProvidesModifiers, ProvidesResourceChanges, GameEventInterface
{
    public function __construct(
        public PlayerId $player,
        public CardDefinition $card,
    ) {
    }

    public function getModifiers(PlayerId $playerId): ModifierCollection
    {
        return new ModifierCollection([]);
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChanges
    {
        if ($this->player->equals($playerId)) {
            return $this->card->resourceChanges;
        }
        return new ResourceChanges();
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            player: PlayerId::fromString($values['player']),
            card: CardDefinition::fromString($values['card']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->player,
            'card' => $this->card->jsonSerialize(),
        ];
    }
}
