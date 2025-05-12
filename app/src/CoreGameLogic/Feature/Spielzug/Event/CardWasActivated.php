<?php

declare(strict_types=1);

namespace Domain\CoreGameLogic\Feature\Spielzug\Event;

use Domain\CoreGameLogic\Dto\ValueObject\CardId;
use Domain\CoreGameLogic\Dto\ValueObject\GuthabenChange;
use Domain\CoreGameLogic\Dto\ValueObject\Modifier;
use Domain\CoreGameLogic\Dto\ValueObject\ModifierCollection;
use Domain\CoreGameLogic\Dto\ValueObject\ModifierId;
use Domain\CoreGameLogic\Dto\ValueObject\PlayerId;
use Domain\CoreGameLogic\Dto\ValueObject\ResourceChangeCollection;
use Domain\CoreGameLogic\EventStore\GameEventInterface;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesModifiers;
use Domain\CoreGameLogic\Feature\Spielzug\Event\Behavior\ProvidesResourceChanges;

final readonly class CardWasActivated implements ProvidesModifiers, ProvidesResourceChanges, GameEventInterface
{
    public function __construct(
        public PlayerId $player,
        public CardId $card,
    ) {
    }

    public function getModifiers(PlayerId $playerId): ModifierCollection
    {
        return new ModifierCollection([]);
    }

    public function getResourceChanges(PlayerId $playerId): ResourceChangeCollection
    {
        if ($this->card->value === "neues Hobby" && $this->player->equals($playerId)) {
            return new ResourceChangeCollection([new GuthabenChange(-500)]);
        }
        return new ResourceChangeCollection([]);
    }

    public static function fromArray(array $values): GameEventInterface
    {
        return new self(
            player: PlayerId::fromString($values['player']),
            card: new CardId($values['card']),
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'player' => $this->player,
            'card' => $this->card,
        ];
    }
}
